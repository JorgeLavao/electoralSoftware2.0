<?php

namespace App\Livewire\Supporters;

use App\Jobs\ImportSupportersJob;
use App\Jobs\ProcessSupportersPreviewJob;
use App\Models\ImportBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
class ImportSupporter extends Component
{
    use WithFileUploads;

    public $file;

    public string $step = 'upload';

    public ?int $batchId = null;

    public array $counts = ['valid' => 0, 'warning' => 0, 'invalid' => 0];
    public array $previewRows = [];
    public int $progress = 0;
    public int $processedRows = 0;
    public ?int $totalRows = null;

    public ?string $status = null;
    public ?string $errorMessage = null;

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:xlsx,xls,csv'],
        ];
    }

    public function updatedFile(): void{
        if (!$this->file) return;
        $this->validate();
        // limpiar estado anterior
        $this->cleanupBatch();
        // guardar archivo (rápido)
        $path = $this->file->store('imports/source', 'local');
        $batch = ImportBatch::create([
            'type' => 'supporters_preview',
            'status' => 'queued',
            'source_path' => $path,
            'counts' => ['valid' => 0, 'warning' => 0, 'invalid' => 0],
            'last_errors' => [],
            'processed_rows' => 0,
        ]);
        $this->batchId = $batch->id;
        $this->step = 'processing';
        ProcessSupportersPreviewJob::dispatch($batch->id)->onConnection('redis');
        $this->refreshBatch();
    }


    public function importValidSupporters(): void
    {
        if (!$this->batchId) {
            $this->alertImport('No hay lote de importación activo.');
            return;
        }
        $batch = ImportBatch::find($this->batchId);
        if (!$batch) {
            $this->alertImport('No se encontró el lote de importación.');
            return;
        }
        if (($batch->counts['valid'] ?? 0) === 0) {
            $this->alertImport('No hay registros válidos para importar.');
            return;
        }

        $campaign_id    = session('current_campaign')->id;
        $reffer_id      = Auth::user()->id;

        ImportSupportersJob::dispatch($batch->id, $campaign_id, $reffer_id)->onConnection('redis');
        // Opcional: puedes cambiar de step o mostrar un mensaje
        $this->dispatch('alert', [
            'icon' => 'success',
            'title' => 'Importación en progreso',
            'text' => 'Estamos importando los simpatizantes válidos en segundo plano.',
            'timer' => 3000,
        ]);
    }


    public function refreshBatch(): void
    {
        if (!$this->batchId) return;

        $batch = ImportBatch::find($this->batchId);
        if (!$batch) return;

        $this->status = $batch->status;
        $this->errorMessage = $batch->error_message;

        $this->counts = $batch->counts ?? ['valid' => 0, 'warning' => 0, 'invalid' => 0];
        $this->previewRows = $batch->last_errors ?? [];

        $this->processedRows = (int)($batch->processed_rows ?? 0);
        $this->totalRows = $batch->total_rows ? (int)$batch->total_rows : null;
        $this->progress = $batch->progress_percent;

        if ($batch->status === 'done') {
            $this->step = 'preview';
        }

        if ($batch->status === 'failed') {
            $this->alertImport($batch->error_message ?: 'El archivo no pudo ser procesado.');
            $this->back();
        }
    }

    public function downloadErrors(): StreamedResponse
    {
        if (!$this->batchId) abort(404);

        $batch = ImportBatch::find($this->batchId);
        if (!$batch || !$batch->errors_csv_path || !Storage::disk('local')->exists($batch->errors_csv_path)) {
            abort(404);
        }
        $fullPath = Storage::disk('local')->path($batch->errors_csv_path);
        return response()->streamDownload(function () use ($fullPath) {
            $in = fopen($fullPath, 'rb');
            while (!feof($in)) {
                echo fread($in, 1024 * 1024);
                flush();
            }
            fclose($in);
        }, 'errores_importacion.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function back(): void
    {
        if ($this->file) {
            try {
                $this->file->delete();
            } catch (\Throwable $e) {
                Log::warning('No se pudo borrar archivo temporal Livewire', [
                    'exception' => $e,
                ]);
            }
        }
        $this->cleanupBatch();
        $this->reset(['file']);
        $this->step = 'upload';
        $this->counts = ['valid' => 0, 'warning' => 0, 'invalid' => 0];
        $this->previewRows = [];
        $this->progress = 0;
        $this->processedRows = 0;
        $this->totalRows = null;
        $this->status = null;
        $this->errorMessage = null;
    }

    private function cleanupBatch(): void
    {
        try {
            if (!$this->batchId) return;
            $batch = ImportBatch::find($this->batchId);
            if ($batch) {
                if ($batch->source_path && Storage::disk('local')->exists($batch->source_path)) {
                    Storage::disk('local')->delete($batch->source_path);
                }
                if ($batch->errors_csv_path && Storage::disk('local')->exists($batch->errors_csv_path)) {
                    Storage::disk('local')->delete($batch->errors_csv_path);
                }
                $batch->delete();
            }
        } catch (\Throwable $e) {
            Log::error('cleanupBatch failed', ['exception' => $e]);
        } finally {
            $this->batchId = null;
        }
    }

    public function alertImport(string $message)
    {
        $this->dispatch('alert', [
            'icon' => 'error',
            'title' => 'No se pudo subir el archivo',
            'text' => $message,
            'timer' => 4000,
        ]);
    }

    public function render()
    {
        return view('livewire.supporters.import-supporter');
    }
}
