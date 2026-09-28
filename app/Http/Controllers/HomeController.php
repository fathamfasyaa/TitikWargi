<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Home page: the public map and the latest reports.
     */
    public function __invoke(): View
    {
        $latestReports = Report::query()
            ->visible()
            ->withCount('supporters')
            ->latest()
            ->limit(10)
            ->get();

        return view('home', ['latestReports' => $latestReports]);
    }
}
