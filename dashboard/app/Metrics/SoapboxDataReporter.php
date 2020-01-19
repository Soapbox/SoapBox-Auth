<?php
namespace App\Metrics;

use Illuminate\Database\DatabaseManager;
use App\Metrics\Queries\Team;
use App\Metrics\Queries\SoapBoxes;
use App\Metrics\Queries\AverageCloseRatio;
use App\Metrics\Queries\AverageMeetingRating;

class SoapboxDataReporter
{
    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    private $database;

    /**
     * Create a new active SoapBox tracker
     *
     * @param \Illuminate\Database\DatabaseManager $database
     */
    public function __construct(DatabaseManager $database)
    {
        $this->database = $database;
    }

    public function getSoapboxesData($days = 30)
    {
        $sql = SoapBoxes::GET_SOAPBOXES_DATA_GIVEN_DAYS;

        $result = $this->database->select($sql, [$days]);

        return $result;
    }

    public function getSoapboxData($slug)
    {
        $sql = SoapBoxes::GET_SOAPBOXES_DATA_GIVEN_SLUG;

        $result = $this->database->select($sql, [$slug]);

        if (count($result) > 0) {
            return $result[0];
        } else {
            return $result;
        }
    }

    public function getAverageCloseRatio($slug = '', $timePeriod = 90)
    {
        //IF SLUG IS DEFINED
        $sqlWithSlug = AverageCloseRatio::GET_AVERAGE_CLOSE_RATIO_GIVEN_SLUG;

        $sqlWithoutSlug = AverageCloseRatio::GET_AVERAGE_CLOSE_RATIO;

        if ($slug != '') {
            $result = $this->database->select($sqlWithSlug, [
                $timePeriod,
                $slug
            ]);
        } else {
            $result = $this->database->select($sqlWithoutSlug, [$timePeriod]);
        }

        return $result;
    }

    public function getAverageMeetingRating($slug = '', $timePeriod = 90)
    {
        //IF SLUG IS DEFINED
        $sqlWithSlug =
            AverageMeetingRating::GET_AVERAGE_MEETING_RATING_GIVEN_SLUG;

        $sqlWithoutSlug = AverageMeetingRating::GET_AVERAGE_MEETING_RATING;

        if ($slug != '') {
            $result = $this->database->select($sqlWithSlug, [
                $timePeriod,
                $slug
            ]);
        } else {
            $result = $this->database->select($sqlWithoutSlug, [$timePeriod]);
        }

        return $result;
    }

    public function getTeamBreakdown(
        $slug,
        $channel_type = 'one-on-one',
        $timePeriod = 90
    ) {
        $sql = Team::GET_BREAKDOWN;

        $result = $this->database->select($sql, [
            $timePeriod,
            $slug,
            $channel_type,
            $slug,
            $channel_type,
            $timePeriod
        ]);
        return $result;
    }

    public function getWhoIsntHavingMeetings(
        $slug,
        $channel_type = 'one-on-one',
        $timePeriod = 90
    ) {
        $sql = Team::GET_WHO_HAS_NO_MEETINGS;

        $result = $this->database->select($sql, [
            $slug,
            $channel_type,
            $timePeriod
        ]);
        return $result;
    }
}
