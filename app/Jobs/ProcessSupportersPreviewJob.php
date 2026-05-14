<?php

namespace App\Jobs;

use App\Imports\SupportersPreviewImport;
use App\Models\ImportBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProcessSupportersPreviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $batchId;

    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }

    public function handle(): void
    {
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;

        $docKey = "import:batch:{$batch->id}:doc";
        $emailKey = "import:batch:{$batch->id}:email";

        $batch->status = 'processing';
        $batch->started_at = now();
        $batch->counts = ['valid' => 0, 'warning' => 0, 'invalid' => 0];
        $batch->last_errors = [];
        $batch->processed_rows = 0;
        $batch->error_message = null;
        $batch->save();

        try {
            $fullPath = Storage::disk('local')->path($batch->source_path);
            $batch->total_rows = $this->estimateTotalRows($fullPath, 'Usuarios');
            $batch->save();

            // CSV de errores
            $errorsCsvPath = 'imports/errors/' . Str::uuid() . '.csv';
            $batch->errors_csv_path = $errorsCsvPath;
            $batch->save();

            // Limpiar sets de duplicados
            Redis::del($docKey, $emailKey);
            Redis::expire($docKey, 21600);   // 6 horas
            Redis::expire($emailKey, 21600); // 6 horas

            $import = new SupportersPreviewImport(
                batchId: $batch->id,
                errorsCsvPath: $errorsCsvPath,
                lastErrorsLimit: 20,
                docKey: $docKey,
                emailKey: $emailKey
            );

            Excel::import($import, $fullPath);

            if ($import->missingUsuariosSheet) {
                throw new \RuntimeException('El archivo debe contener una hoja llamada "Usuarios".');
            }

            $batch->refresh();
            $batch->status = 'done';
            $batch->finished_at = now();

            if (empty($batch->total_rows)) {
                $batch->total_rows = $batch->processed_rows;
            }

            $batch->save();
        } catch (\Throwable $e) {
            $batch->status = 'failed';
            $batch->error_message = $e->getMessage();
            $batch->finished_at = now();
            $batch->save();
        } finally {
            try {
                Redis::del($docKey, $emailKey);
            } catch (\Throwable $e) {
                return;
            }
        }
    }

    private function estimateTotalRows(string $filePath, string $sheetName): ?int
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($ext === 'csv') {
            $fh = fopen($filePath, 'r');
            if (!$fh) return null;
            $lines = 0;
            while (!feof($fh)) {
                fgets($fh);
                $lines++;
            }
            fclose($fh);
            return max(0, $lines - 1);
        }

        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $info = $reader->listWorksheetInfo($filePath);

            foreach ($info as $sheet) {
                if (($sheet['worksheetName'] ?? null) === $sheetName) {
                    $rows = (int)($sheet['totalRows'] ?? 0);
                    return max(0, $rows - 1);
                }
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}
