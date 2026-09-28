<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportSupportController extends Controller
{
    /**
     * "Saya juga terdampak": add the support, or remove it if it was already given.
     */
    public function __invoke(Request $request, Report $report): RedirectResponse
    {
        Gate::authorize('support', $report);

        $result = $report->supporters()->toggle($request->user());

        $message = $result['attached'] === []
            ? 'Dukungan Anda dibatalkan.'
            : 'Terima kasih, dukungan Anda tercatat.';

        return redirect()->route('reports.show', $report)->with('status', $message);
    }
}
