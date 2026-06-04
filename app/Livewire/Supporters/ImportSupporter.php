<?php

namespace App\Livewire\Supporters;

use App\Jobs\ImportSupportersJob;
use App\Jobs\ProcessSupportersPreviewJob;
use App\Models\Campaign;
use App\Models\ImportBatch;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
class ImportSupporter extends Component
{
    use WithFileUploads, AuthorizesRequests;

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
    public ?string $lastAlertedStatus = null;

    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        $this->authorize('importSupporters', $campaign);
        $this->campaign = $campaign;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:xlsx,xls,csv'],
        ];
    }

    public function updatedFile(): void
    {
        $this->authorize('importSupporters', $this->campaign);

        if (! $this->file) {
            $this->debugImport('updatedFile called without file');
            return;
        }

        $this->debugImport('File selected', $this->fileDebugContext());

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->debugImport('File validation failed', [
                ...$this->fileDebugContext(),
                'errors' => $e->errors(),
            ], 'warning');

            throw $e;
        }

        $this->debugImport('File validation passed', $this->fileDebugContext());
        $this->cleanupBatch();

        $path = $this->file->store('imports/source', 'local');

        $this->debugImport('File stored for preview', [
            ...$this->fileDebugContext(),
            'stored_path' => $path,
            'exists' => Storage::disk('local')->exists($path),
        ]);

        $batch = ImportBatch::create([
            'user_id' => Auth::id(),
            'campaign_id' => $this->campaign->id,
            'type' => 'supporters_preview',
            'status' => 'queued',
            'source_path' => $path,
            'counts' => ['valid' => 0, 'warning' => 0, 'invalid' => 0],
            'last_errors' => [],
            'processed_rows' => 0,
        ]);

        $this->batchId = $batch->id;
        $this->step = 'processing';

        $this->debugImport('Preview batch created and job dispatched', [
            'batch_id' => $batch->id,
            'source_path' => $path,
        ]);

        ProcessSupportersPreviewJob::dispatch($batch->id);
        $this->refreshBatch();
    }

    public function importValidSupporters(): void
    {
        $this->authorize('importSupporters', $this->campaign);

        if (! $this->batchId) {
            $this->debugImport('Import requested without active batch', [], 'warning');
            $this->alertImport('No hay lote de importación activo.');
            return;
        }

        $batch = $this->currentBatch();

        if (! $batch) {
            $this->debugImport('Import requested but batch was not found', [
                'batch_id' => $this->batchId,
            ], 'warning');
            $this->alertImport('No se encontró el lote de importación.');
            return;
        }

        if (($batch->counts['valid'] ?? 0) === 0) {
            $this->debugImport('Import requested with zero valid rows', $this->batchDebugContext($batch), 'warning');
            $this->alertImport('No hay registros válidos para importar.');
            return;
        }

        $this->debugImport('Import job dispatched', $this->batchDebugContext($batch));

        ImportSupportersJob::dispatch($batch->id, $this->campaign->id, (int) Auth::id());
        $this->step = 'importing';
        $this->status = 'importing';
        $this->progress = 0;
        $this->processedRows = 0;

        $this->dispatch('alert', [
            'icon' => 'success',
            'title' => 'Importacion en progreso',
            'text' => $this->queuedImportMessage($batch),
            'timer' => 3000,
        ]);
    }

    public function refreshBatch(): void
    {
        if (! $this->batchId) {
            return;
        }

        $batch = $this->currentBatch();

        if (! $batch) {
            return;
        }

        $this->status = $batch->status;
        $this->errorMessage = $batch->error_message;
        $this->counts = $batch->counts ?? ['valid' => 0, 'warning' => 0, 'invalid' => 0];
        $this->previewRows = $batch->last_errors ?? [];
        $this->processedRows = (int) ($batch->processed_rows ?? 0);
        $this->totalRows = $batch->total_rows ? (int) $batch->total_rows : null;
        $this->progress = $batch->progress_percent;

        $this->dispatch('import-debug', [
            'message' => 'Batch refreshed',
            'level' => 'debug',
            'context' => $this->batchDebugContext($batch),
        ]);

        if ($batch->status === 'done') {
            $this->step = 'preview';
        }

        if ($batch->status === 'importing') {
            $this->step = 'importing';
        }

        if ($batch->status === 'import_done') {
            if ($this->lastAlertedStatus !== 'import_done') {
                $this->lastAlertedStatus = 'import_done';

                $message = $this->finishedImportMessage($batch);
                session()->flash('success', $message);
                $this->cleanupBatch();
                $this->redirectRoute('supporter.index', $this->campaign->code, navigate: true);
            }
        }

        if ($batch->status === 'import_failed') {
            $this->step = 'preview';

            if ($this->lastAlertedStatus !== 'import_failed') {
                $this->lastAlertedStatus = 'import_failed';
                $this->alertImport($batch->error_message ?: 'No se pudo importar el archivo.');
            }
        }

        if ($batch->status === 'failed') {
            $this->debugImport('Preview batch failed', $this->batchDebugContext($batch), 'error');
            $this->alertImport($batch->error_message ?: 'El archivo no pudo ser procesado.');
            $this->back();
        }
    }

    public function downloadErrors(): StreamedResponse
    {
        if (! $this->batchId) {
            abort(404);
        }

        $batch = $this->currentBatch();

        if (! $batch || ! $batch->errors_csv_path || ! Storage::disk('local')->exists($batch->errors_csv_path)) {
            abort(404);
        }

        $fullPath = Storage::disk('local')->path($batch->errors_csv_path);

        return response()->streamDownload(function () use ($fullPath) {
            $in = fopen($fullPath, 'rb');

            while (! feof($in)) {
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
        $this->lastAlertedStatus = null;
    }

    private function cleanupBatch(): void
    {
        try {
            if (! $this->batchId) {
                return;
            }

            $batch = $this->currentBatch();

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

    public function alertImport(string $message): void
    {
        $this->debugImport('Import alert shown', [
            'message' => $message,
            'status' => $this->status,
        ], 'warning');

        $this->dispatch('alert', [
            'icon' => 'error',
            'title' => 'No se pudo subir el archivo',
            'text' => $message,
            'timer' => 4000,
        ]);
    }

    private function currentBatch(): ?ImportBatch
    {
        if (! $this->batchId) {
            return null;
        }

        return ImportBatch::query()
            ->whereKey($this->batchId)
            ->where('user_id', Auth::id())
            ->where('campaign_id', $this->campaign->id)
            ->first();
    }

    private function queuedImportMessage(ImportBatch $batch): string
    {
        $invalidCount = (int) ($batch->counts['invalid'] ?? 0);
        $validCount = (int) ($batch->counts['valid'] ?? 0);

        if ($invalidCount <= 0) {
            return "Estamos importando {$validCount} simpatizante(s) validos en segundo plano.";
        }

        return "Estamos importando {$validCount} simpatizante(s) validos. {$invalidCount} fila(s) duplicada(s) o invalidas no se importaran.";
    }

    private function finishedImportMessage(ImportBatch $batch): string
    {
        $invalidCount = (int) ($batch->counts['invalid'] ?? 0);
        $validCount = (int) ($batch->counts['valid'] ?? 0);

        if ($invalidCount <= 0) {
            return "Se importaron {$validCount} simpatizante(s) correctamente.";
        }

        return "Se importaron {$validCount} simpatizante(s). {$invalidCount} fila(s) duplicada(s) o invalidas fueron omitidas.";
    }

    private function fileDebugContext(): array
    {
        if (! $this->file) {
            return [
                'file_present' => false,
            ];
        }

        $size = method_exists($this->file, 'getSize') ? $this->file->getSize() : null;

        return [
            'file_present' => true,
            'original_name' => method_exists($this->file, 'getClientOriginalName') ? $this->file->getClientOriginalName() : null,
            'client_extension' => method_exists($this->file, 'getClientOriginalExtension') ? $this->file->getClientOriginalExtension() : null,
            'mime_type' => method_exists($this->file, 'getMimeType') ? $this->file->getMimeType() : null,
            'size_bytes' => $size,
            'size_mb' => $size ? round($size / 1024 / 1024, 2) : null,
            'max_rule_kb' => 10240,
            'max_rule_mb' => 10,
        ];
    }

    private function batchDebugContext(ImportBatch $batch): array
    {
        return [
            'batch_id' => $batch->id,
            'campaign_id' => $batch->campaign_id,
            'user_id' => $batch->user_id,
            'status' => $batch->status,
            'total_rows' => $batch->total_rows,
            'processed_rows' => $batch->processed_rows,
            'progress' => $batch->progress_percent,
            'counts' => $batch->counts,
            'source_path' => $batch->source_path,
            'source_exists' => $batch->source_path ? Storage::disk('local')->exists($batch->source_path) : false,
            'errors_csv_path' => $batch->errors_csv_path,
            'error_message' => $batch->error_message,
        ];
    }

    private function debugImport(string $message, array $context = [], string $level = 'debug'): void
    {
        $context = [
            'component' => self::class,
            'campaign_id' => $this->campaign->id ?? null,
            'user_id' => Auth::id(),
            'batch_id' => $this->batchId,
            ...$context,
        ];

        Log::log($level, "[supporters-import] {$message}", $context);

        $this->dispatch('import-debug', [
            'message' => $message,
            'level' => $level,
            'context' => $context,
        ]);
    }

    public function render()
    {
        $this->authorize('importSupporters', $this->campaign);

        return view('livewire.supporters.import-supporter');
    }
}
