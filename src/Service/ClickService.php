<?php
declare(strict_types = 1);

namespace App\Service;

use App\Models\Click;
use App\Models\Link;
use App\Models\Client;
use App\Models\PixelLog;

/**
 * Class ClickService
 */
class ClickService
{
    /**
     * @param PixelLog $pixel_log
     * @param Link     $link
     * @param Client   $client
     *
     * @return bool
     */
    public static function saveClick(PixelLog $pixel_log, Link $link, Client $client): bool
    {
        $click = self::getOrCreateClick($pixel_log);

        $click->pp_id        = $pixel_log->pp_id;
        $click->partner_id   = $link->partner_id;
        $click->link_id      = $link->id;
        $click->client_id    = $client->id;
        $click->click_id     = $pixel_log->data['click_id'] ?? null;
        $click->web_id       = $pixel_log->data['utm_term'] ?? null;
        $click->pixel_log_id = $pixel_log->id;

        return $click->save();

    }

    /**
     * @param PixelLog $pixel_log
     *
     * @return Click
     */
    private static function getOrCreateClick(PixelLog $pixel_log): Click
    {
        // Тут мы проверяем, что данная запись не существовала до этого в таблице clicks
        return Click::query()
                ->where('pp_id', '=', $pixel_log->pp_id)
                ->where('pixel_log_id', '=', $pixel_log->id)
                ->first() ?? new Click();
    }
}
