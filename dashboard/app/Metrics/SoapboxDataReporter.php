<?php
namespace App\Metrics;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\DatabaseManager;

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
        $sql = 'SELECT count(users.id) as user_count, soapboxes.slug, soapboxes.name, soapboxes.domain, soapboxes.last_active_at
        FROM users, soapboxes
        WHERE soapboxes.id = users.soapbox_id
            AND users.deactivated_at IS NULL
            AND users.deleted_at IS NULL
            AND soapboxes.last_active_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
        GROUP BY soapboxes.id
        ORDER BY user_count DESC';

        $result = $this->database->select($sql, [$days]);

        return $result;
    }

    public function getSoapboxData($slug)
    {
        $query = DB::table('users')
            ->select(
                DB::raw('count(users.id) as user_count'),
                'soapboxes.slug',
                'soapboxes.name',
                'soapboxes.domain',
                DB::raw("(SELECT count(users.id) FROM users
                WHERE users.is_deferred = 1
                AND users.deactivated_at IS NULL
                AND users.deleted_at IS NULL
                AND users.soapbox_id = soapboxes.id) as deferred_count"),
                DB::raw("(SELECT count(users.id) FROM users
                WHERE users.deactivated_at IS NOT NULL
                AND users.soapbox_id = soapboxes.id) as deactivated_count"),
            )
            ->join('soapboxes', 'soapboxes.id', '=', 'users.soapbox_id')
            ->whereNull('users.deactivated_at')
            ->whereNull('users.deleted_at')
            ->where('users.is_deferred', '=', 0)
            ->where('soapboxes.slug', '=', $slug)
            ->groupBy('soapboxes.id')
            ->orderBy('user_count');

        return $query->get()->first();
    }

    public function getUsers($slug) {
        $result = $this->database
            ->table(Api::table('users'))
                ->join(Api::table('soapboxes'), 'users.soapbox_id', '=', 'soapboxes.id')
                ->where('soapboxes.slug', '=', $slug)
                ->whereNull('users.deactivated_at')
                ->orderBy('users.is_deferred', 'asc')
                ->orderBy('users.last_active_at', 'desc')
                ->selectRaw('users.name, users.email, users.avatar, users.created_at, users.last_active_at, users.is_deferred')
            ->get();

        return $result->toArray();
    }

    public function getAverageCloseRatio($slug = '', $timePeriod = 90) {
/*
    SELECT DATE(A.created_at) as meeting_date, COUNT(A.id) as total, AVG(A.close_ratio) as close_ratio
    FROM
    (
        SELECT meetings.id, meetings.created_at,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) as closed_items,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NULL) as open_items,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id) as total_items,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) / (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id) as close_ratio
        FROM meetings, channels
        WHERE meetings.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            AND meetings.channel_id = channels.id
            AND channels.soapbox_id IN (SELECT id from soapboxes where slug = 'soapbox' )
        ORDER BY created_at ASC
    ) A
    GROUP BY DATE(A.created_at)
    ORDER BY DATE(A.created_at) ASC
*/
        //IF SLUG IS DEFINED
        $sqlWithSlug = 'SELECT DATE(A.created_at) as meeting_date, COUNT(A.id) as total, AVG(A.close_ratio) as close_ratio
        FROM
        (
            SELECT meetings.id, meetings.created_at,
                (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) as closed_items,
                (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NULL) as open_items,
                (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id) as total_items,
                (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) / (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id) as close_ratio
            FROM meetings, channels
            WHERE meetings.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND meetings.channel_id = channels.id
                AND channels.soapbox_id IN (SELECT id from soapboxes where slug = ? )
            ORDER BY created_at ASC
        ) A
        GROUP BY DATE(A.created_at)
        ORDER BY DATE(A.created_at) ASC';

        $sqlWithoutSlug = 'SELECT DATE(A.created_at) as meeting_date, COUNT(A.id) as total, AVG(A.close_ratio) as close_ratio
        FROM
        (
            SELECT meetings.id, meetings.created_at,
                (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) as closed_items,
                (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NULL) as open_items,
                (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id) as total_items,
                (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) / (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id) as close_ratio
            FROM meetings, channels
            WHERE meetings.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND meetings.channel_id = channels.id
            ORDER BY created_at ASC
        ) A
        GROUP BY DATE(A.created_at)
        ORDER BY DATE(A.created_at) ASC';

        if ($slug != '') {
            $result = $this->database->select($sqlWithSlug, [$timePeriod, $slug]);
        } else {
            $result = $this->database->select($sqlWithoutSlug, [$timePeriod]);
        }

        return $result;
    }

    public function getAverageMeetingRating($slug = '', $timePeriod = 90) {
/*
        SELECT DATE(A.created_at) as meeting_date, COUNT(A.id) as total, AVG(A.meeting_rating) as close_ratio
        FROM
        (
            SELECT meetings.id, meetings.created_at,
                (SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) as responses,
                (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL) as count_of_responses,
                ((SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) / (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL)) as meeting_rating
            FROM meetings, channels
            WHERE meetings.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                AND meetings.channel_id = channels.id
                AND channels.soapbox_id IN (SELECT id from soapboxes where slug = 'soapbox' )
            ORDER BY created_at ASC
        ) A
        GROUP BY DATE(A.created_at)
        ORDER BY DATE(A.created_at) ASC
*/
        //IF SLUG IS DEFINED
        $sqlWithSlug = "SELECT DATE(A.created_at) as meeting_date, COUNT(A.id) as total, AVG(A.meeting_rating) as meeting_rating
        FROM
        (
            SELECT meetings.id, meetings.created_at,
                (SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) as responses,
                (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL) as count_of_responses,
                ((SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) / (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL)) as meeting_rating
            FROM meetings, channels
            WHERE meetings.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND meetings.channel_id = channels.id
                AND channels.soapbox_id IN (SELECT id from soapboxes where slug = ? )
            ORDER BY created_at ASC
        ) A
        GROUP BY DATE(A.created_at)
        ORDER BY DATE(A.created_at) ASC";

        $sqlWithoutSlug = "SELECT DATE(A.created_at) as meeting_date, COUNT(A.id) as total, AVG(A.meeting_rating) as meeting_rating
        FROM
        (
            SELECT meetings.id, meetings.created_at,
                (SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) as responses,
                (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL) as count_of_responses,
                ((SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) / (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL)) as meeting_rating
            FROM meetings, channels
            WHERE meetings.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND meetings.channel_id = channels.id
            ORDER BY created_at ASC
        ) A
        GROUP BY DATE(A.created_at)
        ORDER BY DATE(A.created_at) ASC";

        if ($slug != '') {
            $result = $this->database->select($sqlWithSlug, [$timePeriod, $slug]);
        } else {
            $result = $this->database->select($sqlWithoutSlug, [$timePeriod]);
        }

        return $result;
    }




    public function getTeamBreakdown($slug, $channel_type = 'one-on-one', $timePeriod = 90) {
/*
SELECT A.user_id, A.name, A.avatar, A.num_of_channels, A.num_of_meetings, B.average_agenda_items, B.average_close_ratio, B.average_meeting_rating
FROM
(
    SELECT users.id as user_id, users.name, users.email, users.avatar,
        COUNT(MCU.channel_id) as num_of_channels,
        SUM((SELECT count(id) FROM meetings WHERE channel_id = MCU.channel_id AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY) )) as num_of_meetings
    FROM users, channels, channel_user MCU, soapboxes
    WHERE 0 = 0
        AND soapboxes.slug = 'soapbox'
        AND channels.soapbox_id = soapboxes.id
        AND channels.type = 'one-on-one'
        AND MCU.channel_id = channels.id
        AND MCU.type = 'manager'
        AND users.id = MCU.user_id
        AND users.deactivated_at IS NULL
        AND channels.archived_at IS NULL
    GROUP BY users.id
    ORDER BY users.name
) A
LEFT JOIN
(
    SELECT users.id as user_id, COUNT(meetings.id) as num_of_meetings,
        AVG((SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL)) as average_closed_items,
        AVG((SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id)) as average_agenda_items,
        AVG((SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) / (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id)) as average_close_ratio,
        AVG((SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL)) as average_count_of_responses,
        AVG(((SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) / (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL))) as average_meeting_rating
    FROM users, channels, channel_user MCU, meetings, soapboxes
    WHERE 0 = 0
        AND soapboxes.slug = 'soapbox'
        AND channels.soapbox_id = soapboxes.id
        AND channels.type = 'one-on-one'
        AND meetings.channel_id = channels.id
        AND meetings.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
        AND MCU.channel_id = channels.id
        AND MCU.type = 'manager'
        AND users.id = MCU.user_id
        AND users.deactivated_at IS NULL
        AND channels.archived_at IS NULL
    GROUP BY users.id
    ORDER BY users.name
) B
ON A.user_id = B.user_id
*/
        $sql = "SELECT A.user_id as user_id, A.name as name, A.avatar as avatar, A.num_of_channels as num_of_channels, A.num_of_meetings as num_of_meetings, B.average_agenda_items as average_agenda_items, B.average_close_ratio as average_close_ratio, B.average_meeting_rating/5 as average_meeting_rating
        FROM
        (
            SELECT users.id as user_id, users.name, users.email, users.avatar,
                COUNT(MCU.channel_id) as num_of_channels,
                SUM((SELECT count(id) FROM meetings WHERE channel_id = MCU.channel_id AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) )) as num_of_meetings
            FROM users, channels, channel_user MCU, soapboxes
            WHERE 0 = 0
                AND soapboxes.slug = ?
                AND channels.soapbox_id = soapboxes.id
                AND channels.type = ?
                AND MCU.channel_id = channels.id
                AND MCU.type = 'manager'
                AND users.id = MCU.user_id
                AND users.deactivated_at IS NULL
                AND channels.archived_at IS NULL
            GROUP BY users.id
            ORDER BY users.name
        ) A
        LEFT JOIN
        (
            SELECT users.id as user_id, COUNT(meetings.id) as num_of_meetings,
                AVG((SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL)) as average_closed_items,
                AVG((SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id)) as average_agenda_items,
                AVG((SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) / (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id)) as average_close_ratio,
                AVG((SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL)) as average_count_of_responses,
                AVG(((SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) / (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL))) as average_meeting_rating
            FROM users, channels, channel_user MCU, meetings, soapboxes
            WHERE 0 = 0
                AND soapboxes.slug = ?
                AND channels.soapbox_id = soapboxes.id
                AND channels.type = ?
                AND meetings.channel_id = channels.id
                AND meetings.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND MCU.channel_id = channels.id
                AND MCU.type = 'manager'
                AND users.id = MCU.user_id
                AND users.deactivated_at IS NULL
                AND channels.archived_at IS NULL
            GROUP BY users.id
            ORDER BY users.name
        ) B
        ON A.user_id = B.user_id";

        $result = $this->database->select($sql, [$timePeriod, $slug, $channel_type, $slug, $channel_type, $timePeriod]);
        return $result;
    }

    public function getWhoIsntHavingMeetings($slug, $channel_type = 'one-on-one', $timePeriod = 90) {
/*
SELECT users.id, users.name, users.avatar, MAX(meetings.created_at)
FROM users, channels, channel_user MCU, meetings, soapboxes
WHERE 0 = 0
    AND soapboxes.slug = 'soapbox'
    AND channels.soapbox_id = soapboxes.id
    AND channels.type = 'one-on-one'
    AND meetings.channel_id = channels.id
    AND meetings.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
    AND MCU.channel_id = channels.id
    AND MCU.type = 'manager'
    AND users.id = MCU.user_id
    AND users.deactivated_at IS NULL
    AND channels.archived_at IS NULL
GROUP BY users.id
*/

        $sql = "SELECT users.id, users.name, users.avatar, MAX(meetings.created_at) as max_created_at
        FROM users, channels, channel_user MCU, meetings, soapboxes
        WHERE 0 = 0
            AND soapboxes.slug = ?
            AND channels.soapbox_id = soapboxes.id
            AND channels.type = ?
            AND meetings.channel_id = channels.id
            AND meetings.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND MCU.channel_id = channels.id
            AND MCU.type = 'manager'
            AND users.id = MCU.user_id
            AND users.deactivated_at IS NULL
            AND channels.archived_at IS NULL
        GROUP BY users.id
        ORDER BY MAX(meetings.created_at) ASC";

        $result = $this->database->select($sql, [$slug, $channel_type, $timePeriod]);
        return $result;
    }


    public function getOverviewDataForSlug($slug)
    {
        /*
SELECT channels.id as channel_id, channels.latest_activity_at, channels.next_meeting,
  MU.name as manager_name, MU.email as manager_email, MU.id as manager_user_id,
  EU.name as employee_name, EU.email as employee_email, EU.id as employee_user_id
FROM channels, channel_user MCU, channel_user ECU, users MU, users EU
WHERE 0 = 0
  AND channels.type = 'one-on-one'
  AND channels.soapbox_id = 2
  AND channels.archived_at IS NULL
  AND channels.id = MCU.channel_id
  AND channels.id = ECU.channel_id
  AND MU.id = MCU.user_id
  AND EU.id = ECU.user_id
  AND MCU.type = 'manager'
  AND ECU.type = 'employee'
  AND EU.deactivated_at IS NULL
  AND MU.deactivated_at IS NULL
        */

        $result = $this->database
            ->table(Api::table('channels'))
            ->join(Api::table('channel_user AS MCU'), 'channels.id', '=', 'MCU.channel_id')
            ->join(Api::table('channel_user AS ECU'), 'channels.id', '=', 'ECU.channel_id')
            ->join(Api::table('users AS MU'), 'MCU.user_id', '=', 'MU.id')
            ->join(Api::table('users AS EU'), 'ECU.user_id', '=', 'EU.id')
            ->join(Api::table('soapboxes'), 'channels.soapbox_id', '=', 'soapboxes.id')
            ->where('channels.type', '=', 'one-on-one')
            ->whereNull('channels.archived_at')
            ->whereNull('EU.deactivated_at')
            ->whereNull('MU.deactivated_at')
            ->where('MCU.type', '=', 'manager')
            ->where('ECU.type', '=', 'employee')
            ->where('soapboxes.slug', '=', $slug)
            ->selectRaw('channels.id as channel_id, channels.latest_activity_at, channels.next_meeting,
            MU.name as manager_name, MU.email as manager_email, MU.id as manager_user_id,
            EU.name as employee_name, EU.email as employee_email, EU.id as employee_user_id')
            ->get();

        return $result->toArray();
    }
}
