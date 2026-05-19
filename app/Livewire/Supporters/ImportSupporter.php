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
            return;
        }

        $this->validate();
        $this->cleanupBatch();

        $path = $this->file->store('imports/source', 'local');

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

        ProcessSupportersPreviewJob::dispatch($batch->id);
        $this->refreshBatch();
    }

    public function importValidSupporters(): void
    {
        $this->authorize('importSupporters', $this->campaign);

        if (! $this->batchId) {
            $this->alertImport('No hay lote de importación activo.');
            return;
        }

        $batch = $this->currentBatch();

        if (! $batch) {
            $this->alertImport('No se encontró el lote de importación.');
            return;
        }

        if (($batch->counts['valid'] ?? 0) === 0) {
            $this->alertImport('No hay registros válidos para importar.');
            return;
        }

        ImportSupportersJob::dispatch($batch->id, $this->campaign->id, (int) Auth::id());

        $this->dispatch('alert', [
            'icon' => 'success',
            'title' => 'Importación en progreso',
            'text' => 'Estamos importando los simpatizantes válidos en segundo plano.',
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

    public function render()
    {
        $this->authorize('importSupporters', $this->campaign);

        return view('livewire.supporters.import-supporter');
    }
}
