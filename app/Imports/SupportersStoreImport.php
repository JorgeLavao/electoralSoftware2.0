<?php

namespace App\Imports;

use App\Models\DocumentType;
use App\Models\User;

class SupportersStoreImport extends AbstractSupportersImport
{
    protected int $imported = 0;

    protected int $lastErrorsLimit = 20;

    protected int $campaignId;

    protected int $refferId;
    /**
     * Cache local de código -> id de tipo de documento
     *
     * @var array<string,int>
     */
    protected static array $documentTypeMap = [];


    public function __construct( int $batchId, int $campaignId,int $refferId, string $docKey = '', string $emailKey = '')
    {
        parent::__construct($batchId, $docKey, $emailKey);
        $this->campaignId = $campaignId;
        $this->refferId = $refferId;
    }



    protected function handleRowResult( int $rowIndex, array $data, string $status, array $messages): void
    {
        if ($status !== 'valid') {
            return;
        }

        $tipoCodigo = strtoupper($data['tipo_de_documento'] ?? '');
        $doc        = $data['numero_de_documento'] ?? null;
        $email      = mb_strtolower(trim((string)($data['correo_electronico'] ?? '')));

        $documentTypeId = $this->resolveDocumentTypeId($tipoCodigo);

        if (!$documentTypeId) {

            $messages[] = "Tipo de documento '{$tipoCodigo}' no existe en la tabla document_types";

            $this->counts['valid']   = max(0, $this->counts['valid'] - 1);
            $this->counts['invalid'] = ($this->counts['invalid'] ?? 0) + 1;

            $issue = [
                'row' => $rowIndex,
                'tipo_de_documento' => $tipoCodigo,
                'nro_documento' => (string)$doc,
                'estado' => 'invalid',
                'mensaje' => implode(' | ', $messages),
            ];
            $this->pushLastError($issue);
            return;
        }

        $existingByDoc = User::where('document_type_id', $documentTypeId)->where('document_number', $doc)->first();

        if ($email !== '') {
            $emailInUse = User::where('email', $email)
                ->when($existingByDoc, fn ($query) => $query->where('id', '!=', $existingByDoc->id))
                ->exists();

            if ($emailInUse) {
                $this->counts['valid']   = max(0, $this->counts['valid'] - 1);
                $this->counts['invalid'] = ($this->counts['invalid'] ?? 0) + 1;
                $messages[] = 'Correo ya está en uso por otro simpatizante';
                $issue = [
                    'row' => $rowIndex,
                    'tipo_de_documento' => $tipoCodigo,
                    'nro_documento' => (string)$doc,
                    'estado' => 'invalid',
                    'mensaje' => implode(' | ', $messages),
                ];
                $this->pushLastError($issue);
                return;
            }
        }

        $user = User::updateOrCreate(
            [
                'document_type_id' => $documentTypeId,
                'document_number' => $doc, // ajusta a tu nombre real de columna si hace falta
            ],
            [
                'first_name'     => $data['primer_nombre'] ?? null,
                'middle_name'    => $data['segundo_nombre'] ?? null,
                'paternal_surname'   => $data['primer_apellido'] ?? null,
                'maternal_surname'  => $data['segundo_apellido'] ?? null,
                'celphone'           => $data['numero_de_celular'] ?? null,
                'email'             => $email ?: null,
            ]
        );


        if(!$user){
            $this->counts['valid']   = max(0, $this->counts['valid'] - 1);
            $this->counts['invalid'] = ($this->counts['invalid'] ?? 0) + 1;
            $messages[] = 'No se encontro el usuario';
            $issue = [
                'row' => $rowIndex,
                'tipo_de_documento' => $tipoCodigo,
                'nro_documento' => (string)$doc,
                'estado' => 'invalid',
                'mensaje' => implode(' | ', $messages),
            ];
            $this->pushLastError($issue);
            return;
        }

        $campaignMembership = $user->supporter_campaigns()
            ->where('campaigns.id', $this->campaignId)
            ->first();

        if ($campaignMembership && (int) $campaignMembership->pivot->validate !== 2) {
            $this->counts['valid']   = max(0, $this->counts['valid'] - 1);
            $this->counts['invalid'] = ($this->counts['invalid'] ?? 0) + 1;
            $messages[] = 'El usuario ya hace parte de esta camapaña';
            $issue = [
                'row' => $rowIndex,
                'tipo_de_documento' => $tipoCodigo,
                'nro_documento' => (string)$doc,
                'estado' => 'invalid',
                'mensaje' => implode(' | ', $messages),
            ];
            $this->pushLastError($issue);
            return;
        }
        
        $pivotData = [
            'reffer_by' => $this->refferId,
            'approach' => 4,
            'validate' => 0,
        ];

        if ($campaignMembership) {
            $user->supporter_campaigns()->updateExistingPivot($this->campaignId, $pivotData);
        } else {
            $user->supporter_campaigns()->attach($this->campaignId, $pivotData);
        }

        $this->imported++;
    }

    /**
     * Resuelve el id de document_types a partir del código (CC, TI, etc.),
     * usando cache estático para no consultar la BD en cada fila.
     */
    protected function resolveDocumentTypeId(?string $code): ?int
    {
        if (!$code) {
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
        parent::__destruct();
        // $batch = \App\Models\ImportBatch::find($this->batchId);
        // if ($batch) {
        //     $batch->imported_rows = $this->imported;
        //     $batch->save();
        // }
    }
}
