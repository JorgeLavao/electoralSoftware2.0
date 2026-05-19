<?php

namespace App\Jobs;

use App\Imports\SupportersStoreImport;
use App\Models\ImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
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
            $batch->finished_at = now();
            $batch->save();
        } catch (\Throwable $e) {
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
}
