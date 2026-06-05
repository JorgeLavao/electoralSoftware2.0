<?php

namespace App\Jobs;

use App\Exports\QueuedFilteredUsersExport;
use App\Models\Campaign;
use App\Models\ExportBatch;
use App\Services\SimpleTablePdf;
use App\Services\SupporterListQueryService;
use App\Services\SupporterRowMapper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportFilteredUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $batchId;

    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
    }

    public function handle(SupporterListQueryService $queryService): void
    {
        $batch = ExportBatch::query()->find($this->batchId);

        if (! $batch) {
            return;
        }

        $batch->forceFill([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ])->save();

        try {
            $campaign = Campaign::query()->findOrFail($batch->campaign_id);
            $query = $queryService->build($campaign, $campaign, $batch->filters ?? []);
            $totalRows = (clone $query)->count();

            if ($batch->scope === ExportBatch::SCOPE_CURRENT_PAGE) {
                $page = max(1, (int) $batch->page);
                $perPage = max(1, (int) $batch->per_page);
                $offset = ($page - 1) * $perPage;
                $totalRows = max(0, min($perPage, $totalRows - $offset));
                $query->forPage($page, $perPage);
            }

            $extension = $batch->format === 'pdf' ? 'pdf' : 'xlsx';
            $path = 'exports/filtered-users-' . $batch->id . '-' . now()->format('Ymd_His') . '.' . $extension;

            if ($batch->format === 'pdf') {
                $users = $query->get();
                $rowMapper = app(SupporterRowMapper::class);
                $roleNamesByUser = $rowMapper->roleNamesByUser($campaign, $users);
                $referrerNamesByUser = $rowMapper->referrerNamesByUser($campaign, $users);
                $referralCountsByUser = $rowMapper->referralCountsByUser($campaign, $users);
                $referrerIdsByUser = $rowMapper->referrerIdsByUser($campaign, $users);
                $headings = (new QueuedFilteredUsersExport($batch->id))->headings();

                $rows = $users->map(function ($user) use ($batch, $rowMapper, $roleNamesByUser, $referrerNamesByUser, $referralCountsByUser, $referrerIdsByUser) {
                    $row = $rowMapper->map($user, $roleNamesByUser, $referrerNamesByUser, $referralCountsByUser, $referrerIdsByUser);

                    return array_values($rowMapper->onlyColumns($row, $batch->columns ?? []));
                });

                Storage::disk('local')->put($path, app(SimpleTablePdf::class)->output(
                    'Listado filtrado - ' . $campaign->name,
                    $headings,
                    $rows->all()
                ));
            } else {
                Excel::store(new QueuedFilteredUsersExport($batch->id), $path, 'local');
            }

            $batch->forceFill([
                'status' => 'done',
                'total_rows' => $totalRows,
                'processed_rows' => $totalRows,
                'file_path' => $path,
                'finished_at' => now(),
            ])->save();
        } catch (\Throwable $e) {
            $batch->forceFill([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ])->save();

            throw $e;
        }
    }
}
