<?php

namespace App\Http\Services;

use App\Charts\LineChart;
use App\Metrics\SoapboxDataReporter;

class DashboardService
{
    private $dataReporter;

    public function __construct()
    {
        $this->dataReporter = app(SoapboxDataReporter::class);
    }

    public function getAllDates($startDate, $endDate)
    {
        $all_dates = array();
        while ($startDate->lte($endDate)) {
            $all_dates[] = $startDate->toDateString();
            $startDate->addDay();
        }
        return $all_dates;
    }

    public function getMeetingRatingBenchmarkChart(
        $all_dates,
        $averageMeetingRatingData
    ) {
        $meetingRatingBenchmarkChart = new LineChart();
        $meetingRatingBenchmarkChart->labels(array_keys($all_dates));
        $meetingRatingBenchmarkChart->displayLegend(false);
        $meetingRatingBenchmarkChart
            ->dataset('Company', 'line', $averageMeetingRatingData['Company'])
            ->color('#108cff')
            ->fill(true)
            ->lineTension(0.4)
            ->options([
                'pointRadius' => 0
            ]);
        $meetingRatingBenchmarkChart
            ->dataset('Industry', 'line', $averageMeetingRatingData['Industry'])
            ->color('#ffc854')
            ->fill(true)
            ->lineTension(0.4)
            ->options([
                'pointRadius' => 0
            ]);
        $meetingRatingBenchmarkChart
            ->dataset(
                'Benchmark',
                'line',
                $averageMeetingRatingData['Benchmark']
            )
            ->color('#c1c1ce')
            ->fill(true)
            ->dashed([10])
            ->options([
                'pointRadius' => 0
            ]);

        return $meetingRatingBenchmarkChart;
    }

    public function getCloseRatioBenchmarkChart(
        $all_dates,
        $averageCloseRatioData
    ) {
        $closeRatioBenchmarkChart = new LineChart();
        $closeRatioBenchmarkChart->labels(array_keys($all_dates));
        $closeRatioBenchmarkChart->displayLegend(false);
        $closeRatioBenchmarkChart
            ->dataset('Company', 'line', $averageCloseRatioData['Company'])
            ->color('#108cff')
            ->fill(true)
            ->lineTension(0.4)
            ->options([
                'pointRadius' => 0
            ]);
        $closeRatioBenchmarkChart
            ->dataset('Industry', 'line', $averageCloseRatioData['Industry'])
            ->color('#ffc854')
            ->fill(true)
            ->lineTension(0.4)
            ->options([
                'pointRadius' => 0
            ]);
        $closeRatioBenchmarkChart
            ->dataset('Benchmark', 'line', $averageCloseRatioData['Benchmark'])
            ->color('#c1c1ce')
            ->fill(true)
            ->dashed([10])
            ->options([
                'pointRadius' => 0
            ]);

        return $closeRatioBenchmarkChart;
    }

    public function getMeetingRatingScoreDetails(
        $companyMeetingRatingCount,
        $averageMeetingRatingCompany,
        $companyMeetingRatingSum,
        $companyMeetingRatingChartArray
    ) {
        if (
            $companyMeetingRatingCount > 0 &&
            $averageMeetingRatingCompany > 0
        ) {
            $meetingRatingScore = sprintf(
                "%.0f%%",
                ($companyMeetingRatingSum / $companyMeetingRatingCount) * 100
            );
            $first45RatioMR =
                $companyMeetingRatingChartArray[0] /
                (count($averageMeetingRatingCompany) / 2);
            $second45RatioMR =
                $companyMeetingRatingChartArray[1] /
                (count($averageMeetingRatingCompany) / 2);
            $meetingRatingPercentageChange =
                $second45RatioMR > 0
                    ? (1 - $first45RatioMR / $second45RatioMR) * 100
                    : 0;
        } else {
            $meetingRatingPercentageChange = 0;
            $meetingRatingScore = 0;
        }

        return (object) [
            'score' => $meetingRatingScore,
            'percentage_change' => $meetingRatingPercentageChange
        ];
    }

    public function getMeetingRatingChart($all_dates, $averageMeetingRatingData)
    {
        $meetingRatingChart = new LineChart();
        $meetingRatingChart->labels(array_keys($all_dates));
        $meetingRatingChart->minimalist(true);
        $meetingRatingChart
            ->dataset('Team', 'line', $averageMeetingRatingData['Company'])
            ->color('#25ecad')
            ->fill(true)
            ->lineTension(0.4)
            ->options([
                'pointRadius' => 0,
                'responsive' => true,
                'maintainAspectRatio' => false
            ]);

        return $meetingRatingChart;
    }

    public function getCloseRatioDetails(
        $companyAverageCloseRatioCount,
        $averageCloseRatioCompany,
        $companyAverageCloseRatioSum,
        $companyAverageCloseRatioChartArray
    ) {
        if (
            $companyAverageCloseRatioCount > 0 &&
            $averageCloseRatioCompany > 0
        ) {
            $closeRatioScore = sprintf(
                "%.0f%%",
                ($companyAverageCloseRatioSum /
                    $companyAverageCloseRatioCount) *
                    100
            );
            $first45Ratio =
                $companyAverageCloseRatioChartArray[0] /
                (count($averageCloseRatioCompany) / 2);
            $second45Ratio =
                $companyAverageCloseRatioChartArray[1] /
                (count($averageCloseRatioCompany) / 2);
            $closeRatioPercentageChange =
                $second45Ratio > 0
                    ? (1 - $first45Ratio / $second45Ratio) * 100
                    : 0;
        } else {
            $closeRatioScore = 0;
            $closeRatioPercentageChange = 0;
        }

        return (object) [
            'score' => $closeRatioScore,
            'percentage_change' => $closeRatioPercentageChange
        ];
    }

    public function getCloseRatioChart($all_dates, $averageCloseRatioData)
    {
        $closeRatioChart = new LineChart();
        $closeRatioChart->labels(array_keys($all_dates));
        $closeRatioChart->minimalist(true);
        $closeRatioChart
            ->dataset('Team', 'line', $averageCloseRatioData['Company'])
            ->color('#25ecad')
            ->fill(true)
            ->lineTension(0.4)
            ->options([
                'pointRadius' => 0,
                'responsive' => true,
                'maintainAspectRatio' => false
            ]);

        return $closeRatioChart;
    }

    public function getUsersNotHavingMeeting($slug, $DURATION)
    {
        $users_not_having_meetings = $this->dataReporter->getWhoIsntHavingMeetings(
            $slug,
            'one-on-one',
            $DURATION
        );

        return array_slice($users_not_having_meetings, 0, 5);
    }

    public function getUsersWorstAverageMeetingRating($users)
    {
        $users_worst_average_meeting_rating = [];
        foreach ($users as $user) {
            //users less than 75% show up here.
            if (
                $user->average_meeting_rating &&
                $user->average_meeting_rating < 0.75
            ) {
                $users_worst_average_meeting_rating[] = $user;
            }
        }
        usort($users_worst_average_meeting_rating, function ($item1, $item2) {
            return $item1->average_meeting_rating <=>
                $item2->average_meeting_rating;
        });
        return array_slice($users_worst_average_meeting_rating, 0, 5);
    }

    public function getUsersBestCloseRatio($users)
    {
        usort($users, function ($item1, $item2) {
            return $item2->average_close_ratio <=> $item1->average_close_ratio;
        });

        return array_slice($users, 0, 5);
    }

    public function getAverageMeetingRatingData(
        $all_dates,
        $averageCloseRatioCompany,
        $averageCloseRatioIndustry,
        &$companyAverageCloseRatioCount,
        $averageMeetingRatingCompany,
        $averageMeetingRatingIndustry,
        &$companyMeetingRatingCount,
        &$companyAverageCloseRatioSum,
        &$companyAverageCloseRatioChartArray,
        &$companyMeetingRatingSum,
        &$companyMeetingRatingChartArray,
        &$averageCloseRatioData
    ) {
        $averageMeetingRatingData = [];

        foreach ($all_dates as $key => $date) {
            //Close Ratio Work
            $crTmpCompany = $this->findWhere($averageCloseRatioCompany, array(
                'meeting_date' => $date
            ));
            $crTmpIndustry = $this->findWhere($averageCloseRatioIndustry, array(
                'meeting_date' => $date
            ));

            $cr_company = 0.0;
            if ($crTmpCompany) {
                $cr_company = $crTmpCompany->close_ratio;

                $companyAverageCloseRatioSum += $crTmpCompany->close_ratio;
                if (
                    $companyAverageCloseRatioCount <
                    count($averageCloseRatioCompany) / 2
                ) {
                    $companyAverageCloseRatioChartArray[0] +=
                        $crTmpCompany->close_ratio;
                } else {
                    $companyAverageCloseRatioChartArray[1] +=
                        $crTmpCompany->close_ratio;
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
            $tmpMeetingRatingCompany = $this->findWhere(
                $averageMeetingRatingCompany,
                array('meeting_date' => $date)
            );
            $tmpMeetingRatingIndustry = $this->findWhere(
                $averageMeetingRatingIndustry,
                array('meeting_date' => $date)
            );

            $mr_company = 0.0;
            if ($tmpMeetingRatingCompany) {
                $mr_company = $tmpMeetingRatingCompany->meeting_rating / 5;

                $companyMeetingRatingSum +=
                    $tmpMeetingRatingCompany->meeting_rating / 5;
                if (
                    $companyMeetingRatingCount <
                    count($averageMeetingRatingCompany) / 2
                ) {
                    $companyMeetingRatingChartArray[0] +=
                        $tmpMeetingRatingCompany->meeting_rating / 5;
                } else {
                    $companyMeetingRatingChartArray[1] +=
                        $tmpMeetingRatingCompany->meeting_rating / 5;
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

        return $averageMeetingRatingData;
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
                    } elseif ($item->$key != $value) {
                        $is_match = false;
                        break;
                    }
                } else {
                    if (!isset($item[$key])) {
                        $is_match = false;
                        break;
                    } elseif ($item[$key] != $value) {
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
