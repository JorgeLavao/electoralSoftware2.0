<?php

namespace App\Jobs;

use App\Imports\SupportersStoreImport;
use App\Models\Campaign;
use App\Models\ImportBatch;
use App\Services\CampaignNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportSupportersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $batchId;
    public int $campaignId;
    public int $referrerId;

    public function __construct(int $batchId, int $campaignId, int $referrerId)
    {
        $this->batchId    = $batchId;
        $this->campaignId = $campaignId;
        $this->referrerId = $referrerId;
    }

    public function handle(): void
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;

        if ((int) $batch->campaign_id !== $this->campaignId || (int) $batch->user_id !== $this->referrerId) {
            Log::warning('[supporters-import] Import job rejected by ownership check', [
                'batch_id' => $batch->id,
                'batch_campaign_id' => $batch->campaign_id,
                'requested_campaign_id' => $this->campaignId,
                'batch_user_id' => $batch->user_id,
                'requested_referrer_id' => $this->referrerId,
            ]);

            $batch->status = 'import_failed';
            $batch->error_message = 'El lote de importación no pertenece a la campaña o al usuario solicitante.';
            $batch->finished_at = now();
            $batch->save();
            return;
        }

        // Usamos claves distintas en Redis para la fase de importación
        $docKey = "import:batch:{$batch->id}:doc:import";
        $emailKey = "import:batch:{$batch->id}:email:import";

        $batch->status = 'importing';
        $batch->error_message = null;
        $batch->processed_rows = 0; // opcional: reseteas el conteo para la fase de import
        $batch->counts = ['valid' => 0, 'warning' => 0, 'invalid' => 0];
        $batch->save();

        try {
            $fullPath = Storage::disk('local')->path($batch->source_path);
            Log::debug('[supporters-import] Import job started', [
                'batch_id' => $batch->id,
                'campaign_id' => $this->campaignId,
                'referrer_id' => $this->referrerId,
                'source_path' => $batch->source_path,
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath),
                'file_size_bytes' => file_exists($fullPath) ? filesize($fullPath) : null,
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'memory_limit' => ini_get('memory_limit'),
            ]);

            // Limpiar sets de duplicados para ESTA corrida
            Redis::del($docKey, $emailKey);
            Redis::expire($docKey, 21600);
            Redis::expire($emailKey, 21600);

            $import = new SupportersStoreImport(
                batchId: $batch->id,
                campaignId: $this->campaignId,
                refferId: $this->referrerId,
                docKey: $docKey,
                emailKey: $emailKey
            );
            Excel::import($import, $fullPath);

            $batch->refresh();
            $batch->status = 'import_done';
            $batch->error_message = $this->importSummaryMessage($batch);
            $batch->finished_at = now();
            $batch->save();

            $campaign = Campaign::query()->find($this->campaignId);

            if ($campaign) {
                $validCount = (int) ($batch->counts['valid'] ?? 0);
                $invalidCount = (int) ($batch->counts['invalid'] ?? 0);

                app(CampaignNotificationService::class)->notifyCampaignPermission(
                    $campaign,
                    'campaign.supporters.validate',
                    [
                        'title' => 'Importacion finalizada',
                        'body' => "Se importaron {$validCount} simpatizante(s) en {$campaign->name}. {$invalidCount} fila(s) fueron omitidas.",
                        'icon' => 'success',
                        'url' => route('supporter.index', $campaign->code, absolute: false),
                        'priority' => 'important',
                    ],
                    [$this->referrerId]
                );
            }

            Log::debug('[supporters-import] Import job finished', [
                'batch_id' => $batch->id,
                'status' => $batch->status,
                'processed_rows' => $batch->processed_rows,
                'counts' => $batch->counts,
                'error_message' => $batch->error_message,
                'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);
        } catch (\Throwable $e) {
            Log::error('[supporters-import] Import job failed', [
                'batch_id' => $batch->id,
                'source_path' => $batch->source_path,
                'message' => $e->getMessage(),
                'exception' => $e,
                'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);

            $batch->status = 'import_failed';
            $batch->error_message = $e->getMessage();
            $batch->finished_at = now();
            $batch->save();
        } finally {
            try {
                Redis::del($docKey, $emailKey);
            } catch (\Throwable $e) {
                // ignoramos error de limpieza de Redis
            }
        }
    }

    private function importSummaryMessage(ImportBatch $batch): ?string
    {
        $invalidCount = (int) ($batch->counts['invalid'] ?? 0);
        $validCount = (int) ($batch->counts['valid'] ?? 0);

        if ($invalidCount <= 0) {
            return null;
        }

        return "Se importaron {$validCount} simpatizante(s). {$invalidCount} fila(s) duplicada(s) o invalidas fueron omitidas.";
    }
}
