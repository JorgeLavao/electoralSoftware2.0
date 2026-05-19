<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\ExportBatch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DownloadExportController extends Controller
{
    public function __invoke(ExportBatch $exportBatch)
    {
        abort_unless((int) $exportBatch->user_id === (int) Auth::id(), 403);
        abort_unless($exportBatch->status === 'done', 404);
        abort_unless($exportBatch->file_path && Storage::disk('local')->exists($exportBatch->file_path), 404);

        return Storage::disk('local')->download($exportBatch->file_path);
    }
}
