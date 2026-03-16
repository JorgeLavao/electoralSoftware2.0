<?php

namespace App\Imports;

use App\Mail\InviteToCampaign;
use App\Models\Campaign;
use App\Models\Supporter;
use App\Models\DocumentType;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        if (!$existingByDoc && $email !== '') {
            $emailInUse = User::where('email', $email)->exists();

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

        $alreadyInCampaign = $user->foreign_campaings()->where('campaign_id', $this->campaignId)->exists();
        if($alreadyInCampaign){
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
        
        Invitation::where('user_id', $user->id)->where('active', true)->update(['active' => false]);
        $token = Str::uuid()->toString();
        //create invitation
        $invitation = new Invitation();
        $invitation->user_id    = $user->id;
        $invitation->campaign_id= $this->campaignId;
        $invitation->expires_at = now()->addHours(48);
        $invitation->reffer_id  = $this->refferId;
        $invitation->token      = $token;
        $invitation->active     = true;
        $invitation->save();

        //send email
        $campaign = Campaign::find($this->campaignId);

        Mail::to($user->email)->send(new InviteToCampaign($campaign, $user->first_name, $invitation->token, $invitation->expires_at));
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
