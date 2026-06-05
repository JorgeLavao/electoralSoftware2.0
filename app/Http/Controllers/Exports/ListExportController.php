<?php

namespace App\Http\Controllers\Exports;

use App\Exports\ListUsersExport;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignList;
use App\Services\SimpleTablePdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ListExportController extends Controller
{
    public function __invoke(Request $request, Campaign $campaign, CampaignList $list)
    {
        $this->authorize('exportLists', $campaign);
        abort_unless((int) $list->campaign_id === (int) $campaign->id, 404);

        $format = $request->query('format', 'xlsx');
        abort_unless(in_array($format, ['xlsx', 'pdf'], true), 422);

        $filename = (string) str($list->name ?: 'listado')
            ->ascii()
            ->slug('-')
            ->limit(80, '');
        $filename = $filename !== '' ? $filename : 'listado';

        $export = new ListUsersExport($list);

        if ($format === 'pdf') {
            $pdf = app(SimpleTablePdf::class)->output(
                'Listado: ' . $list->name,
                $export->headings(),
                $export->collection()->all()
            );

            return response()->streamDownload(
                fn () => print($pdf),
                $filename . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        }

        return Excel::download($export, $filename . '.xlsx');
    }
}
