<?php
declare(strict_types = 1);

namespace App\Service;

use App\Models\Link;
use App\Models\PixelLog;

/**
 * Class LinkService
 */
class LinkService
{
    /**
     * @param PixelLog $pixel_log
     *
     * @return Link|null
     */
    public static function getLink(PixelLog $pixel_log): ?Link
    {
        // Обрабатываем тот момент, когда url содержит в себе наши UTM-метки
        return Link::query()
            ->where('pp_id', '=', $pixel_log->pp_id)
            ->where('id', '=', $pixel_log->data['utm_campaign'])
            ->where('partner_id', '=', $pixel_log->data['utm_content'])
            ->first();
    }
}
