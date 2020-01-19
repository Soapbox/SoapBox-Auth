<?php

namespace App\Metrics\Queries;

class AverageMeetingRating
{
    const GET_AVERAGE_MEETING_RATING = "SELECT DATE(A.created_at) as meeting_date, 
        COUNT(A.id) as total, AVG(A.meeting_rating) as meeting_rating
    FROM (
        SELECT meetings.id, meetings.created_at,
            (SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) 
                as responses,
            (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL) 
                as count_of_responses,
            ((SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) 
                / (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL)) 
                as meeting_rating
        FROM meetings, channels
        WHERE meetings.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND meetings.channel_id = channels.id
        ORDER BY created_at ASC
    ) A
    GROUP BY DATE(A.created_at)
    ORDER BY DATE(A.created_at) ASC";

    const GET_AVERAGE_MEETING_RATING_GIVEN_SLUG = "SELECT DATE(A.created_at) as meeting_date, 
        COUNT(A.id) as total, AVG(A.meeting_rating) as meeting_rating
    FROM (
        SELECT meetings.id, meetings.created_at,
            (SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) 
                as responses,
            (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL) 
                as count_of_responses,
            ((SELECT sum(response) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id) 
                / (SELECT count(user_id) FROM meeting_rating_user WHERE meeting_rating_id = meetings.id AND response IS NOT NULL)) 
                as meeting_rating
        FROM meetings, channels
        WHERE meetings.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND meetings.channel_id = channels.id
            AND channels.soapbox_id IN (SELECT id from soapboxes where slug = ? )
        ORDER BY created_at ASC
    ) A
    GROUP BY DATE(A.created_at)
    ORDER BY DATE(A.created_at) ASC";
}
