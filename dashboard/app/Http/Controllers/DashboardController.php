<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $dataReporter;

    public function __construct()
    {
    }

    /**
     * Show the dashboard for a logged in user.
     *
     * @param  \Illuminate\Http\Request;  $request
     * @return View
     */
    public function show(Request $request)
    {
        $days = 30;
        if ($request->has('days')) {
            $days = $request->days;
        }

        $soapboxes = [];

        $startOf2018 = Carbon::createFromFormat('Y-m-d', '2018-01-01');
        $startOf2019 = Carbon::createFromFormat('Y-m-d', '2019-01-01');
        $thisYear = $startOf2019->diffInDays(Carbon::now());
        $allTime = $startOf2018->diffInDays(Carbon::now());

        return view('welcome', compact('soapboxes', 'thisYear', 'allTime'));
    }
}
