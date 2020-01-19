<?php

namespace App\Metrics\Queries;

class Team
{
    const GET_BREAKDOWN = "SELECT A.user_id as user_id, A.name as name, A.avatar as avatar, A.num_of_channels as num_of_channels, A.num_of_meetings as num_of_meetings, B.average_agenda_items as average_agenda_items, B.average_close_ratio as average_close_ratio, B.average_meeting_rating/5 as average_meeting_rating
    FROM
    (
        SELECT users.id as user_id, users.name, users.email, users.avatar,
            COUNT(MCU.channel_id) as num_of_channels,
            SUM((SELECT count(id) FROM meetings WHERE channel_id = MCU.channel_id AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) )) as num_of_meetings
        FROM users, channels, channel_user MCU, soapboxes
        WHERE soapboxes.slug = ?
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
        WHERE soapboxes.slug = ?
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

    const GET_WHO_HAS_NO_MEETINGS = "SELECT users.id, users.name, users.avatar, 
        MAX(meetings.created_at) as max_created_at
    FROM users, channels, channel_user MCU, meetings, soapboxes
    WHERE soapboxes.slug = ?
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
}
