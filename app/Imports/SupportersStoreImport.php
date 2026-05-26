<?php

namespace App\Imports;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SupportersStoreImport extends AbstractSupportersImport
{
    protected int $imported = 0;

    protected int $lastErrorsLimit = 20;

    protected int $storeBufferLimit = 500;

    protected int $campaignId;

    protected int $refferId;

    protected array $pendingRows = [];

    /**
     * Cache local de código -> id de tipo de documento.
     *
     * @var array<string,int>
     */
    protected static array $documentTypeMap = [];

    public function __construct(int $batchId, int $campaignId, int $refferId, string $docKey = '', string $emailKey = '')
    {
        parent::__construct($batchId, $docKey, $emailKey);
        $this->campaignId = $campaignId;
        $this->refferId = $refferId;
    }

    protected function handleRowResult(int $rowIndex, array $data, string $status, array $messages): void
    {
        if ($status !== 'valid') {
            return;
        }

        $tipoCodigo = strtoupper($data['tipo_de_documento'] ?? '');
        $doc = (string) ($data['numero_de_documento'] ?? '');
        $email = mb_strtolower(trim((string) ($data['correo_electronico'] ?? '')));
        $documentTypeId = $this->resolveDocumentTypeId($tipoCodigo);

        if (! $documentTypeId) {
            $this->markRowInvalid(
                $rowIndex,
                $tipoCodigo,
                $doc,
                [...$messages, "Tipo de documento '{$tipoCodigo}' no existe en la tabla document_types"]
            );

            return;
        }

        $this->pendingRows[] = [
            'rowIndex' => $rowIndex,
            'data' => $data,
            'messages' => $messages,
            'tipoCodigo' => $tipoCodigo,
            'documentTypeId' => $documentTypeId,
            'doc' => $doc,
            'email' => $email,
        ];

        if (count($this->pendingRows) >= $this->storeBufferLimit) {
            $this->storePendingRows();
        }
    }

    protected function storePendingRows(): void
    {
        if ($this->pendingRows === []) {
            return;
        }

        $rows = $this->pendingRows;
        $this->pendingRows = [];

        $usersByDocument = $this->preloadUsersByDocument($rows);
        $usersByEmail = $this->preloadUsersByEmail($rows);
        $storedRows = [];

        foreach ($rows as $row) {
            $documentKey = $this->documentKey($row['documentTypeId'], $row['doc']);
            $existingByDoc = $usersByDocument[$documentKey] ?? null;

            if ($existingByDoc) {
                $this->markPendingRowInvalid($row, "Documento {$row['tipoCodigo']} {$row['doc']} ya existe en la base de datos");
                continue;
            }

            if ($row['email'] !== '') {
                $emailOwner = $usersByEmail[$row['email']] ?? null;

                if ($emailOwner) {
                    $this->markPendingRowInvalid($row, "Correo '{$row['email']}' ya esta en uso por otro simpatizante");
                    continue;
                }
            }

            try {
                $user = $this->storeUser($row, $existingByDoc);
            } catch (QueryException $e) {
                if ($this->isDocumentUniqueConstraintViolation($e)) {
                    $this->markPendingRowInvalid($row, "Documento {$row['tipoCodigo']} {$row['doc']} ya existe en la base de datos");
                    continue;
                }

                if ($this->isEmailUniqueConstraintViolation($e)) {
                    $this->markPendingRowInvalid($row, "Correo '{$row['email']}' ya esta en uso por otro simpatizante");
                    continue;
                }

                throw $e;
            }

            if (! $user) {
                $this->markPendingRowInvalid($row, 'No se encontró el usuario');
                continue;
            }

            $usersByDocument[$documentKey] = $user;

            if ($row['email'] !== '') {
                $usersByEmail[$row['email']] = $user;
            }

            $storedRows[] = [
                'row' => $row,
                'user' => $user,
            ];
        }

        $this->syncCampaignMemberships($storedRows);
    }

    protected function preloadUsersByDocument(array $rows): array
    {
        $documentsByType = collect($rows)
            ->groupBy('documentTypeId')
            ->map(fn ($typeRows) => $typeRows->pluck('doc')->filter()->unique()->values()->all())
            ->filter()
            ->all();

        if ($documentsByType === []) {
            return [];
        }

        return User::query()
            ->where(function ($query) use ($documentsByType) {
                foreach ($documentsByType as $documentTypeId => $documents) {
                    $query->orWhere(function ($documentQuery) use ($documentTypeId, $documents) {
                        $documentQuery
                            ->where('document_type_id', (int) $documentTypeId)
                            ->whereIn('document_number', $documents);
                    });
                }
            })
            ->get()
            ->keyBy(fn (User $user) => $this->documentKey((int) $user->document_type_id, (string) $user->document_number))
            ->all();
    }

    protected function preloadUsersByEmail(array $rows): array
    {
        $emails = collect($rows)
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            return [];
        }

        return User::query()
            ->whereIn('email', $emails)
            ->get()
            ->keyBy(fn (User $user) => mb_strtolower((string) $user->email))
            ->all();
    }

    protected function storeUser(array $row, ?User $existingByDoc): ?User
    {
        $attributes = [
            'first_name' => $row['data']['primer_nombre'] ?? null,
            'middle_name' => $row['data']['segundo_nombre'] ?? null,
            'paternal_surname' => $row['data']['primer_apellido'] ?? null,
            'maternal_surname' => $row['data']['segundo_apellido'] ?? null,
            'celphone' => $row['data']['numero_de_celular'] ?? null,
            'email' => $row['email'] ?: null,
        ];

        if ($existingByDoc) {
            $existingByDoc->fill($attributes);
            $existingByDoc->save();

            return $existingByDoc;
        }

        try {
            return User::query()->create($attributes + [
                'document_type_id' => $row['documentTypeId'],
                'document_number' => $row['doc'],
            ]);
        } catch (QueryException $e) {
            throw $e;
        }
    }

    protected function syncCampaignMemberships(array $storedRows): void
    {
        if ($storedRows === []) {
            return;
        }

        $userIds = collect($storedRows)
            ->pluck('user.id')
            ->filter()
            ->unique()
            ->values();

        $memberships = DB::table('campaign_user')
            ->where('campaign_id', $this->campaignId)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $now = now();
        $newMemberships = [];

        foreach ($storedRows as $storedRow) {
            /** @var User $user */
            $user = $storedRow['user'];
            $row = $storedRow['row'];
            $membership = $memberships[$user->id] ?? null;

            if ($membership && (int) $membership->validate !== 2) {
                $this->markPendingRowInvalid($row, 'El usuario ya hace parte de esta campaña');
                continue;
            }

            $pivotData = [
                'reffer_by' => $this->refferId,
                'approach' => 4,
                'validate' => 0,
                'updated_at' => $now,
            ];

            if ($membership) {
                DB::table('campaign_user')
                    ->where('campaign_id', $this->campaignId)
                    ->where('user_id', $user->id)
                    ->update($pivotData);
            } else {
                $newMemberships[] = $pivotData + [
                    'campaign_id' => $this->campaignId,
                    'user_id' => $user->id,
                    'created_at' => $now,
                ];
            }

            $this->imported++;
        }

        if ($newMemberships !== []) {
            DB::table('campaign_user')->insertOrIgnore($newMemberships);
        }
    }

    protected function markPendingRowInvalid(array $row, string $message): void
    {
        $this->markRowInvalid(
            $row['rowIndex'],
            $row['tipoCodigo'],
            $row['doc'],
            [...$row['messages'], $message],
            $row['email'] ?? ''
        );
    }

    protected function markRowInvalid(int $rowIndex, string $tipoCodigo, string $doc, array $messages, string $email = ''): void
    {
        $this->counts['valid'] = max(0, $this->counts['valid'] - 1);
        $this->counts['invalid'] = ($this->counts['invalid'] ?? 0) + 1;

        $this->pushLastError([
            'row' => $rowIndex,
            'tipo_de_documento' => $tipoCodigo,
            'nro_documento' => $doc,
            'correo_electronico' => $email,
            'estado' => 'invalid',
            'mensaje' => implode(' | ', $messages),
        ]);
    }

    protected function documentKey(int $documentTypeId, string $documentNumber): string
    {
        return $documentTypeId . '|' . $documentNumber;
    }

    protected function isDocumentUniqueConstraintViolation(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'users_document_type_number_unique')
            || str_contains($message, 'users.document_type_id')
            || str_contains($message, 'document_type_id, document_number');
    }

    protected function isEmailUniqueConstraintViolation(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'users_email_unique')
            || str_contains($message, 'users.email')
            || str_contains($message, 'duplicate entry')
            || str_contains($message, 'unique constraint')
            || str_contains($message, 'email');
    }

    /**
     * Resuelve el id de document_types a partir del código (CC, TI, etc.),
     * usando cache estático para no consultar la BD en cada fila.
     */
    protected function resolveDocumentTypeId(?string $code): ?int
    {
        if (! $code) {
            return null;
        }

        if (empty(self::$documentTypeMap)) {
            self::$documentTypeMap = DocumentType::query()
                ->pluck('id', 'code')
                ->toArray();
        }

        return self::$documentTypeMap[$code] ?? null;
    }

    /**
     * Guarda solo los últimos N errores para mostrarlos luego,
     * igual que en el preview.
     */
    protected function pushLastError(array $issue): void
    {
        $this->lastErrors[] = $issue;

        if (count($this->lastErrors) > $this->lastErrorsLimit) {
            array_shift($this->lastErrors);
        }
    }

    public function __destruct()
    {
        $this->storePendingRows();

        parent::__destruct();
    }
}
