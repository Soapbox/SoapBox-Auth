<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Metrics\SoapboxDataReporter;
use App\Charts\LineChart;
use Carbon\Carbon;

class DashboardController extends Controller
{
  private $dataReporter;

  public function __construct()
  {
    $this->dataReporter = app(SoapboxDataReporter::class);
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

    $soapboxes = ($this->dataReporter->getSoapboxesData($days));

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
    $DURATION = 90; //hardcode 90 days

    $soapbox = $this->dataReporter->getSoapboxData($slug);
    $users = $this->dataReporter->getTeamBreakdown($slug, 'one-on-one', $DURATION); //$this->dataReporter->getUsers($slug);
    $users_not_having_meetings = $this->dataReporter->getWhoIsntHavingMeetings($slug, 'one-on-one', $DURATION, 7);
    $users_best_close_ratio = $users;
    $users_worst_average_meeting_rating = []; /// $users;

    foreach ($users as $user) {
      //users less than 75% show up here.
      if ($user->average_meeting_rating && $user->average_meeting_rating < 0.75) {
        $users_worst_average_meeting_rating[] = $user;
      }
    }


    usort($users_best_close_ratio, function ($item1, $item2) {
      return $item2->average_close_ratio <=> $item1->average_close_ratio;
    });

    usort($users_worst_average_meeting_rating, function ($item1, $item2) {
      return $item1->average_meeting_rating <=> $item2->average_meeting_rating;
    });


    $users_best_close_ratio = array_slice($users_best_close_ratio, 0, 5);
    $users_worst_average_meeting_rating = array_slice($users_worst_average_meeting_rating, 0, 5);
    $users_not_having_meetings = array_slice($users_not_having_meetings, 0, 5);

    //get 90 days in dates
    $startDate = Carbon::now()->subDays($DURATION);
    $endDate = Carbon::now();
    $all_dates = array();
    while ($startDate->lte($endDate)) {
      $all_dates[] = $startDate->toDateString();
      $startDate->addDay();
    }

    $averageCloseRatioCompany = $this->dataReporter->getAverageCloseRatio($slug, $DURATION);
    $averageCloseRatioIndustry = $this->dataReporter->getAverageCloseRatio('', $DURATION);
    $averageCloseRatioData = [];

    $companyAverageCloseRatioChartArray = [];
    $companyAverageCloseRatioChartArray[0] = 0;
    $companyAverageCloseRatioChartArray[1] = 0;
    $companyAverageCloseRatioCount = 0;
    $companyAverageCloseRatioSum = 0;

    $averageMeetingRatingCompany = $this->dataReporter->getAverageMeetingRating($slug, $DURATION);
    $averageMeetingRatingIndustry = $this->dataReporter->getAverageMeetingRating('', $DURATION);
    $averageMeetingRatingData = [];

    $companyMeetingRatingChartArray = [];
    $companyMeetingRatingChartArray[0] = 0;
    $companyMeetingRatingChartArray[1] = 0;
    $companyMeetingRatingCount = 0;
    $companyMeetingRatingSum = 0;

    foreach ($all_dates as $key => $date) {
      //Close Ratio Work
      $crTmpCompany = $this->findWhere($averageCloseRatioCompany, array('meeting_date' => $date));
      $crTmpIndustry = $this->findWhere($averageCloseRatioIndustry, array('meeting_date' => $date));

      $cr_company = 0.0;
      if ($crTmpCompany) {
        $cr_company = $crTmpCompany->close_ratio;

        $companyAverageCloseRatioSum += $crTmpCompany->close_ratio;
        if ($companyAverageCloseRatioCount < (count($averageCloseRatioCompany) / 2)) {
          $companyAverageCloseRatioChartArray[0] += $crTmpCompany->close_ratio;
        } else {
          $companyAverageCloseRatioChartArray[1] += $crTmpCompany->close_ratio;
        }
        $companyAverageCloseRatioCount++;
      }
      $averageCloseRatioData['Company'][] = $cr_company * 100;

      $cr_industry = 0.0;
      if ($crTmpIndustry) {
        $cr_industry = $crTmpIndustry->close_ratio;
      }
      $averageCloseRatioData['Industry'][] = $cr_industry * 100;

      $averageCloseRatioData['Benchmark'][] = 0.79 * 100;


      //Meeting Rating Work
      $tmpMeetingRatingCompany = $this->findWhere($averageMeetingRatingCompany, array('meeting_date' => $date));
      $tmpMeetingRatingIndustry = $this->findWhere($averageMeetingRatingIndustry, array('meeting_date' => $date));

      $mr_company = 0.0;
      if ($tmpMeetingRatingCompany) {
        $mr_company = $tmpMeetingRatingCompany->meeting_rating / 5;

        $companyMeetingRatingSum += $tmpMeetingRatingCompany->meeting_rating / 5;
        if ($companyMeetingRatingCount < (count($averageMeetingRatingCompany) / 2)) {
          $companyMeetingRatingChartArray[0] += $tmpMeetingRatingCompany->meeting_rating / 5;
        } else {
          $companyMeetingRatingChartArray[1] += $tmpMeetingRatingCompany->meeting_rating / 5;
        }
        $companyMeetingRatingCount++;
      }
      $averageMeetingRatingData['Company'][] = $mr_company * 100;

      $mr_industry = 0.0;
      if ($tmpMeetingRatingIndustry) {
        $mr_industry = $tmpMeetingRatingIndustry->meeting_rating / 5;
      }
      $averageMeetingRatingData['Industry'][] = $mr_industry * 100;

      $averageMeetingRatingData['Benchmark'][] = 0.75 * 100;
    }

    //-- Mini Charts

    //Close Ratio
    $closeRatioChart = new LineChart;
    $closeRatioChart->labels(array_keys($all_dates));
    $closeRatioChart->minimalist(true);
    $closeRatioChart->dataset('Team', 'line', $averageCloseRatioData['Company'])->color('#25ecad')->fill(false)->lineTension(0.4)->options([
      'pointRadius' => 0,
      'responsive' => true,
      'maintainAspectRatio' => false,
    ]);

    if ($companyAverageCloseRatioCount > 0 && $averageCloseRatioCompany > 0) {
      $closeRatioScore = sprintf("%.0f%%", ($companyAverageCloseRatioSum / $companyAverageCloseRatioCount) * 100);
      $first45Ratio = $companyAverageCloseRatioChartArray[0] / (count($averageCloseRatioCompany) / 2);
      $second45Ratio = $companyAverageCloseRatioChartArray[1] / (count($averageCloseRatioCompany) / 2);
      $closeRatioPercentageChange = $second45Ratio > 0 ? (1 - $first45Ratio / $second45Ratio) * 100 : 0;
    } else {
      $closeRatioScore = 0;
      $closeRatioPercentageChange = 0;
    }

    //meeting Rating
    $meetingRatingChart = new LineChart;
    $meetingRatingChart->labels(array_keys($all_dates));
    $meetingRatingChart->minimalist(true);
    $meetingRatingChart->dataset('Team', 'line', $averageMeetingRatingData['Company'])->color('#25ecad')->fill(false)->lineTension(0.4)->options([
      'pointRadius' => 0,
      'responsive' => true,
      'maintainAspectRatio' => false,
    ]);

    if ($companyMeetingRatingCount > 0 && $averageMeetingRatingCompany > 0) {
      $meetingRatingScore = sprintf("%.0f%%", ($companyMeetingRatingSum / $companyMeetingRatingCount) * 100);
      $first45RatioMR = $companyMeetingRatingChartArray[0] / (count($averageMeetingRatingCompany) / 2);
      $second45RatioMR = $companyMeetingRatingChartArray[1] / (count($averageMeetingRatingCompany) / 2);
      $meetingRatingPercentageChange = $second45Ratio > 0 ? (1 - $first45RatioMR / $second45RatioMR) * 100 : 0;
    } else {
      $meetingRatingPercentageChange = 0;
      $meetingRatingScore = 0;
    }

    //-- Big Charts

    //Close Ratio Benchmark Chart
    $closeRatioBenchmarkChart = new LineChart;
    $closeRatioBenchmarkChart->labels(array_keys($all_dates));
    $closeRatioBenchmarkChart->displayLegend(false);
    $closeRatioBenchmarkChart->dataset('Company', 'line', $averageCloseRatioData['Company'])->color('#108cff')->fill(false)->lineTension(0.4)->options([
      'pointRadius' => 0,
    ]);;
    $closeRatioBenchmarkChart->dataset('Industry', 'line', $averageCloseRatioData['Industry'])->color('#ffc854')->fill(false)->lineTension(0.4)->options([
      'pointRadius' => 0,
    ]);;
    $closeRatioBenchmarkChart->dataset('Benchmark', 'line', $averageCloseRatioData['Benchmark'])->color('#c1c1ce')->fill(false)->dashed([10])->options([
      'pointRadius' => 0,
    ]);;

    //Meeting Rating Benchmark Chart
    $meetingRatingBenchmarkChart = new LineChart;
    $meetingRatingBenchmarkChart->labels(array_keys($all_dates));
    $meetingRatingBenchmarkChart->displayLegend(false);
    $meetingRatingBenchmarkChart->dataset('Company', 'line', $averageMeetingRatingData['Company'])->color('#108cff')->fill(false)->lineTension(0.4)->options([
      'pointRadius' => 0,
    ]);;
    $meetingRatingBenchmarkChart->dataset('Industry', 'line', $averageMeetingRatingData['Industry'])->color('#ffc854')->fill(false)->lineTension(0.4)->options([
      'pointRadius' => 0,
    ]);;
    $meetingRatingBenchmarkChart->dataset('Benchmark', 'line', $averageMeetingRatingData['Benchmark'])->color('#c1c1ce')->fill(false)->dashed([10])->options([
      'pointRadius' => 0,
    ]);;

    return view('dashboard', compact('slug', 'soapbox', 'users', 'users_best_close_ratio', 'users_worst_average_meeting_rating', 'users_not_having_meetings', 'closeRatioScore', 'closeRatioPercentageChange', 'closeRatioChart', 'meetingRatingChart', 'meetingRatingScore', 'meetingRatingPercentageChange', 'closeRatioBenchmarkChart', 'meetingRatingBenchmarkChart'));
  }


  private function findWhere($array, $matching)
  {
    foreach ($array as $item) {
      $is_match = true;
      foreach ($matching as $key => $value) {

        if (is_object($item)) {
          if (!isset($item->$key)) {
            $is_match = false;
            break;
          }
        } else {
          if (!isset($item[$key])) {
            $is_match = false;
            break;
          }
        }

        if (is_object($item)) {
          if ($item->$key != $value) {
            $is_match = false;
            break;
          }
        } else {
          if ($item[$key] != $value) {
            $is_match = false;
            break;
          }
        }
      }

      if ($is_match) {
        return $item;
      }
    }
  }
}
