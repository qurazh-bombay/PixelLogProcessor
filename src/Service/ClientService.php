<?php
declare(strict_types = 1);

namespace App\Service;

use App\Models\Client;
use App\Models\PixelLog;

/**
 * Class ClientService
 */
class ClientService
{
    /**
     * @param PixelLog $pixel_log
     *
     * @return Client
     */
    public static function getClient(PixelLog $pixel_log): Client
    {
        $client        = Client::where('id', '=', $pixel_log->data['uid'])->first() ?? new Client();
        $client->id    = $pixel_log->data['uid'];
        $client->pp_id = $pixel_log->pp_id;

        $client->save();

        return $client;
    }
}
