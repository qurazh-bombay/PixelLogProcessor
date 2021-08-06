<?php
declare(strict_types = 1);

namespace App\Event;

use App\Models\PixelLog;
use App\Models\Client;
use App\Models\Link;

/**
 * Interface EventInterface
 */
interface EventInterface
{
    /**
     * @param PixelLog $pixelLog
     * @param Link     $link
     * @param Client   $client
     *
     * @return self
     */
    public function setConfig(PixelLog $pixelLog, Link $link, Client $client): self;

    /**
     * @return bool
     */
    public function handle(): bool;
}
