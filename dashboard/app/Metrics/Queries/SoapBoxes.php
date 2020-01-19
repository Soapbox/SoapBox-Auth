<?php

namespace App\Metrics\Queries;

class SoapBoxes
{
    const GET_SOAPBOXES_DATA_GIVEN_DAYS = 'SELECT 
        count(users.id) as user_count, soapboxes.slug, soapboxes.name, 
        soapboxes.domain, soapboxes.last_active_at
    FROM users, soapboxes
    WHERE soapboxes.id = users.soapbox_id
        AND users.deactivated_at IS NULL
        AND users.deleted_at IS NULL
        AND soapboxes.last_active_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
    GROUP BY soapboxes.id
    ORDER BY user_count DESC';

    const GET_SOAPBOXES_DATA_GIVEN_SLUG = 'SELECT count(users.id) as user_count, 
        soapboxes.slug, soapboxes.name, soapboxes.domain,
        (SELECT count(id) FROM users WHERE is_deferred = 1 
            AND deactivated_at IS NULL AND deleted_at IS NULL 
            AND soapbox_id = soapboxes.id ) as deferred_count,
        (SELECT count(id) FROM users WHERE deactivated_at IS NOT NULL 
        AND soapbox_id = soapboxes.id ) as deactivated_count
    FROM users, soapboxes
    WHERE soapboxes.id = users.soapbox_id
        AND users.deactivated_at IS NULL
        AND users.deleted_at IS NULL
        AND is_deferred = 0
        AND soapboxes.slug = ?
    GROUP BY soapboxes.id
    ORDER BY user_count DESC';
}
