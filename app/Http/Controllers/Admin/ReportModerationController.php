<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ModerationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ModerationRequest;
use App\Models\ModerationLog;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Moderation actions on a report. Each action is written to moderation_logs.
 */
class ReportModerationController extends Controller
{
    /**
     * Hide the report from the public. Its open flags are closed, because they are handled.
     */
    public function hide(ModerationRequest $request, Report $report): RedirectResponse
    {
        DB::transaction(function () use ($request, $report) {
            $report->forceFill(['hidden_at' => now()])->save();
            $this->closeOpenFlags($report);

            ModerationLog::record($request->user(), $report, ModerationAction::HideReport, $request->validated('reason'));
        });

        return back()->with('status', 'Laporan disembunyikan.');
    }

    /**
     * Show a hidden report to the public again.
     */
    public function unhide(ModerationRequest $request, Report $report): RedirectResponse
    {
        DB::transaction(function () use ($request, $report) {
            $report->forceFill(['hidden_at' => null])->save();

            ModerationLog::record($request->user(), $report, ModerationAction::UnhideReport, $request->validated('reason'));
        });

        return back()->with('status', 'Laporan ditampilkan lagi.');
    }

    /**
     * Close the open flags without hiding the report (for example, the flags were wrong).
     */
    public function resolveFlags(ModerationRequest $request, Report $report): RedirectResponse
    {
        DB::transaction(function () use ($request, $report) {
            $this->closeOpenFlags($report);

            ModerationLog::record($request->user(), $report, ModerationAction::ResolveFlags, $request->validated('reason'));
        });

        return back()->with('status', 'Flag ditandai selesai.');
    }

    private function closeOpenFlags(Report $report): void
    {
        $report->flags()->whereNull('resolved_at')->update(['resolved_at' => now()]);
    }
}
