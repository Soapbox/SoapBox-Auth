<?php

namespace App\Metrics\Queries;

class AverageCloseRatio
{
    const GET_AVERAGE_CLOSE_RATIO = 'SELECT DATE(A.created_at) as meeting_date, COUNT(A.id) as total, AVG(A.close_ratio) as close_ratio
    FROM
    (
        SELECT meetings.id, meetings.created_at,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) 
                as closed_items,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NULL) 
                as open_items,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id) 
                as total_items,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) 
                / (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id) 
                as close_ratio
        FROM meetings, channels
        WHERE meetings.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND meetings.channel_id = channels.id
        ORDER BY created_at ASC
    ) A
    GROUP BY DATE(A.created_at)
    ORDER BY DATE(A.created_at) ASC';

    const GET_AVERAGE_CLOSE_RATIO_GIVEN_SLUG = 'SELECT DATE(A.created_at) as meeting_date, 
        COUNT(A.id) as total, AVG(A.close_ratio) as close_ratio
    FROM (
        SELECT meetings.id, meetings.created_at,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) 
                as closed_items,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NULL) 
                as open_items,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id) 
                as total_items,
            (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id AND closed_at IS NOT NULL) 
                / (SELECT count(item_id) FROM meeting_items WHERE meeting_id = meetings.id) 
                as close_ratio
        FROM meetings, channels
        WHERE meetings.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND meetings.channel_id = channels.id
            AND channels.soapbox_id IN (SELECT id from soapboxes where slug = ? )
        ORDER BY created_at ASC
    ) A
    GROUP BY DATE(A.created_at)
    ORDER BY DATE(A.created_at) ASC';
}
