<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Charts\LineChart;
use Illuminate\Http\Request;
use App\Metrics\SoapboxDataReporter;
use App\Http\Services\DashboardService;

class DashboardController extends Controller
{
    private $dataReporter;

    private $dashboardService;

    public function __construct()
    {
        $this->dataReporter = app(SoapboxDataReporter::class);
        $this->dashboardService = new DashboardService();
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

        $soapboxes = $this->dataReporter->getSoapboxesData($days);

        $startOf2018 = Carbon::createFromFormat('Y-m-d', '2018-01-01');
        $startOf2019 = Carbon::createFromFormat('Y-m-d', '2019-01-01');
        $thisYear = $startOf2019->diffInDays(Carbon::now());
        $allTime = $startOf2018->diffInDays(Carbon::now());

        return view('welcome', compact('soapboxes', 'thisYear', 'allTime'));
    }

    public function showNew()
    {
        return view('dash');
    }

    public function showForSlug($slug)
    {
        $DURATION = 30; //hardcode 90 days

        $soapbox = $this->dataReporter->getSoapboxData($slug);
        $users = $this->dataReporter->getTeamBreakdown(
            $slug,
            'one-on-one',
            $DURATION
        );

        $users_best_close_ratio = $this->dashboardService->getUsersBestCloseRatio(
            $users
        );
        $users_worst_average_meeting_rating = $this->dashboardService->getUsersWorstAverageMeetingRating(
            $users
        );
        $users_not_having_meetings = $this->dashboardService->getUsersNotHavingMeeting(
            $slug,
            $DURATION
        );

        //get $DURATION days in dates
        $startDate = Carbon::now()->subDays($DURATION);
        $endDate = Carbon::now();
        $all_dates = array();
        while ($startDate->lte($endDate)) {
            $all_dates[] = $startDate->toDateString();
            $startDate->addDay();
        }

        $averageCloseRatioCompany = $this->dataReporter->getAverageCloseRatio(
            $slug,
            $DURATION
        );
        $averageCloseRatioIndustry = $this->dataReporter->getAverageCloseRatio(
            '',
            $DURATION
        );
        $averageCloseRatioData = [];

        $companyAverageCloseRatioChartArray = [];
        $companyAverageCloseRatioChartArray[0] = 0;
        $companyAverageCloseRatioChartArray[1] = 0;
        $companyAverageCloseRatioCount = 0;
        $companyAverageCloseRatioSum = 0;

        $averageMeetingRatingCompany = $this->dataReporter->getAverageMeetingRating(
            $slug,
            $DURATION
        );
        $averageMeetingRatingIndustry = $this->dataReporter->getAverageMeetingRating(
            '',
            $DURATION
        );

        $companyMeetingRatingChartArray = [];
        $companyMeetingRatingChartArray[0] = 0;
        $companyMeetingRatingChartArray[1] = 0;
        $companyMeetingRatingCount = 0;
        $companyMeetingRatingSum = 0;

        $averageMeetingRatingData = [];
        $averageMeetingRatingData = $this->dashboardService->getAverageMeetingRatingData(
            $all_dates,
            $averageCloseRatioCompany,
            $averageCloseRatioIndustry,
            $companyAverageCloseRatioCount,
            $averageMeetingRatingCompany,
            $averageMeetingRatingIndustry,
            $companyMeetingRatingCount,
            $companyAverageCloseRatioSum,
            $companyAverageCloseRatioChartArray,
            $companyMeetingRatingSum,
            $companyMeetingRatingChartArray,
            $averageCloseRatioData
        );

        //-- Mini Charts

        //Close Ratio
        $closeRatioChart = $this->dashboardService->getCloseRatioChart(
            $all_dates,
            $averageCloseRatioData
        );

        $closeRatio = $this->dashboardService->getCloseRatioDetails(
            $companyAverageCloseRatioCount,
            $averageCloseRatioCompany,
            $companyAverageCloseRatioSum,
            $companyAverageCloseRatioChartArray
        );

        $closeRatioScore = $closeRatio->score;
        $closeRatioPercentageChange = $closeRatio->percentage_change;

        //meeting Rating
        $meetingRatingChart = $this->dashboardService->getMeetingRatingChart(
            $all_dates,
            $averageMeetingRatingData
        );

        $meetingRatingScore = $this->dashboardService->getMeetingRatingScoreDetails(
            $companyMeetingRatingCount,
            $averageMeetingRatingCompany,
            $companyMeetingRatingSum,
            $companyMeetingRatingChartArray
        );

        $meetingRatingPercentageChange = $meetingRatingScore->percentage_change;
        $meetingRatingScore = $meetingRatingScore->score;

        //-- Big Charts

        //Close Ratio Benchmark Chart
        $closeRatioBenchmarkChart = $this->dashboardService->getCloseRatioBenchmarkChart(
            $all_dates,
            $averageCloseRatioData
        );

        //Meeting Rating Benchmark Chart
        $meetingRatingBenchmarkChart = $this->dashboardService->getMeetingRatingBenchmarkChart(
            $all_dates,
            $averageMeetingRatingData
        );

        return view(
            'dashboard',
            compact(
                'slug',
                'soapbox',
                'users',
                'users_best_close_ratio',
                'users_worst_average_meeting_rating',
                'users_not_having_meetings',
                'closeRatioScore',
                'closeRatioPercentageChange',
                'closeRatioChart',
                'meetingRatingChart',
                'meetingRatingScore',
                'meetingRatingPercentageChange',
                'closeRatioBenchmarkChart',
                'meetingRatingBenchmarkChart'
            )
        );
    }
}
