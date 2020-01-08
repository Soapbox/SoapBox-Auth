<?php

namespace App\Metrics;

use App\Api;
use App\DateTime\DateRange;
use App\DateTime\DayInterval;
use Illuminate\Database\DatabaseManager;

class ActiveSoapBoxesTracker
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

    /**
     * Track how many SoapBoxes are active for the given dayInterval during the given dateRange
     *
     * @param string $metric
     * @param \App\DateTime\DateRange $dateRange
     *        The date range for which active SoapBoxes should be tracked. The start date
     *        is inclusive and the end date is exclusive
     * @param \App\DateTime\DayInterval $dayDayInterval
     *        The interval of days for which active SoapBoxes should be tracked. The start of the
     *        interval is includes and the end is exclusive
     *
     * @return void
     */
    public function track(string $metric, DateRange $dateRange, DayInterval $dayInterval): void
    {
        $start = $dayInterval->getStart();
        $end = $dayInterval->getEnd();

        $result = $this->database
            ->table(Api::table('soapboxes'))
            ->join(Api::table('users'), 'users.soapbox_id', '=', 'soapboxes.id')
            ->join('active_user_history', 'active_user_history.user_id', 'users.id')
            ->whereRaw('DATE_ADD(soapboxes.created_at, INTERVAL ? DAY) >= ?', [$end, $dateRange->getStartDate()])
            ->whereRaw('DATE_ADD(soapboxes.created_at, INTERVAL ? DAY) < ?', [$end, $dateRange->getEndDate()])
            ->where('users.email', 'not like', '%@soapboxhq.%')
            ->whereRaw('active_user_history.last_active_at >= DATE_ADD(soapboxes.created_at, INTERVAL ? DAY)', [$start])
            ->whereRaw('active_user_history.last_active_at < DATE_ADD(soapboxes.created_at, INTERVAL ? DAY)', [$end])
            ->selectRaw('count(distinct(soapboxes.id)) as metric')
            ->pluck('metric')
            ->first();

        $this->database->table('metrics')->insert([
            'metric' => $metric,
            'value' => $result,
            'from_date' => $dateRange->getStartDate(),
            'to_date' => $dateRange->getEndDate(),
        ]);
    }
}
