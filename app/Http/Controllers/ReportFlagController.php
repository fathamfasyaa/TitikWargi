<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportFlagRequest;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;

class ReportFlagController extends Controller
{
    /**
     * "Laporkan konten ini": save a flag for the moderators.
     */
    public function __invoke(StoreReportFlagRequest $request, Report $report): RedirectResponse
    {
        $flag = $report->flags()->make($request->validated());
        $flag->user()->associate($request->user());
        $flag->save();

        return redirect()->route('reports.show', $report)
            ->with('status', 'Terima kasih, laporan Anda akan diperiksa oleh moderator.');
    }
}
