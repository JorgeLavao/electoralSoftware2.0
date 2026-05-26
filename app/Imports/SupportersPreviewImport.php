<?php

namespace App\Imports;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class SupportersPreviewImport extends AbstractSupportersImport
{
    private string $errorsCsvPath;
    private int $lastErrorsLimit;

    private $errorsHandle = null;

    private int $campaignId;
    private int $bufferLimit = 500;
    private array $pendingValidRows = [];

    /**
     * @var array<string,int>
     */
    private static array $documentTypeMap = [];

    public function __construct(
        int $batchId,
        string $errorsCsvPath,
        int $lastErrorsLimit = 20,
        int $campaignId = 0,
        string $docKey = '',
        string $emailKey = ''
    ) {
        parent::__construct($batchId, $docKey, $emailKey);

        $this->errorsCsvPath = $errorsCsvPath;
        $this->lastErrorsLimit = $lastErrorsLimit;
        $this->campaignId = $campaignId;
    }

    protected function handleRowResult(
        int $rowIndex,
        array $data,
        string $status,
        array $messages
    ): void {
        if ($status !== 'warning' && $status !== 'invalid') {
            $this->pendingValidRows[] = [
                'rowIndex' => $rowIndex,
                'data' => $data,
                'tipoCodigo' => strtoupper($data['tipo_de_documento'] ?? ''),
                'doc' => (string) ($data['numero_de_documento'] ?? ''),
                'email' => mb_strtolower(trim((string) ($data['correo_electronico'] ?? ''))),
            ];

            if (count($this->pendingValidRows) >= $this->bufferLimit) {
                $this->validatePendingRowsAgainstDatabase();
            }

            return;
        }

        $this->pushIssue($rowIndex, $data, $status, $messages);
    }

    private function validatePendingRowsAgainstDatabase(): void
    {
        if ($this->pendingValidRows === []) {
            return;
        }

        $rows = $this->pendingValidRows;
        $this->pendingValidRows = [];

        $usersByDocument = $this->preloadUsersByDocument($rows);
        $usersByEmail = $this->preloadUsersByEmail($rows);

        foreach ($rows as $row) {
            $documentTypeId = $this->resolveDocumentTypeId($row['tipoCodigo']);

            if (! $documentTypeId) {
                $this->markValidRowInvalid($row, ["Tipo de documento '{$row['tipoCodigo']}' no existe en la tabla document_types"]);
                continue;
            }

            $existingByDoc = $usersByDocument[$this->documentKey($documentTypeId, $row['doc'])] ?? null;

            if ($existingByDoc) {
                $this->markValidRowInvalid($row, ["Documento {$row['tipoCodigo']} {$row['doc']} ya existe en la base de datos"]);
                continue;
            }

            if ($row['email'] !== '') {
                $emailOwner = $usersByEmail[$row['email']] ?? null;

                if ($emailOwner) {
                    $this->markValidRowInvalid($row, ["Correo '{$row['email']}' ya esta en uso por otro simpatizante"]);
                    continue;
                }
            }
        }
    }

    private function markValidRowInvalid(array $row, array $messages): void
    {
        $this->counts['valid'] = max(0, ($this->counts['valid'] ?? 0) - 1);
        $this->counts['invalid'] = ($this->counts['invalid'] ?? 0) + 1;
        $this->pushIssue($row['rowIndex'], $row['data'], 'invalid', $messages);
    }

    private function pushIssue(int $rowIndex, array $data, string $status, array $messages): void
    {
        $issue = [
            'row' => $rowIndex,
            'tipo_de_documento' => $data['tipo_de_documento'] ?? '',
            'nro_documento' => (string) ($data['numero_de_documento'] ?? ''),
            'correo_electronico' => (string) ($data['correo_electronico'] ?? ''),
            'estado' => $status,
            'mensaje' => $messages ? implode(' | ', $messages) : '-',
        ];

        $this->appendErrorToCsv($issue);
        $this->pushLastError($issue);
    }

    private function pushLastError(array $issue): void
    {
        $this->lastErrors[] = $issue;
        if (count($this->lastErrors) > $this->lastErrorsLimit) {
            array_shift($this->lastErrors);
        }
    }

    private function appendErrorToCsv(array $issue): void
    {
        if ($this->errorsHandle === null) {
            Storage::disk('local')->makeDirectory(dirname($this->errorsCsvPath));
            $fullPath = Storage::disk('local')->path($this->errorsCsvPath);
            $isNew = ! file_exists($fullPath);

            $this->errorsHandle = fopen($fullPath, 'a');

            if ($isNew) {
                fputcsv($this->errorsHandle, ['fila', 'tipo_documento', 'nro_documento', 'correo_electronico', 'estado', 'mensaje']);
            }
        }

        fputcsv($this->errorsHandle, [
            $issue['row'] ?? null,
            $issue['tipo_de_documento'] ?? null,
            $issue['nro_documento'] ?? null,
            $issue['correo_electronico'] ?? null,
            $issue['estado'] ?? null,
            $issue['mensaje'] ?? null,
        ]);
    }

    private function preloadUsersByDocument(array $rows): array
    {
        $documentsByType = collect($rows)
            ->map(function ($row) {
                $documentTypeId = $this->resolveDocumentTypeId($row['tipoCodigo']);

                return $documentTypeId ? ['type' => $documentTypeId, 'doc' => $row['doc']] : null;
            })
            ->filter()
            ->groupBy('type')
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

    private function preloadUsersByEmail(array $rows): array
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

    private function resolveDocumentTypeId(?string $code): ?int
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

    private function documentKey(int $documentTypeId, string $documentNumber): string
    {
        return $documentTypeId . '|' . $documentNumber;
    }

    public function __destruct()
    {
        $this->validatePendingRowsAgainstDatabase();

        parent::__destruct();

        if (is_resource($this->errorsHandle)) {
            fclose($this->errorsHandle);
        }
    }
}
