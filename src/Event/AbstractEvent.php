<?php
declare(strict_types = 1);

namespace App\Event;

use App\Models\PixelLog;
use App\Models\Client;
use App\Models\Link;

/**
 * Class AbstractEvent
 */
abstract class AbstractEvent implements EventInterface
{
    /**
     * @var PixelLog
     */
    protected $pixelLog;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Link
     */
    protected $link;

    /**
     * @var string|null
     */
    public $debugMessage;

    /**
     * @param PixelLog $pixelLog
     * @param Link     $link
     * @param Client   $client
     *
     * @return self
     */
    public function setConfig(PixelLog $pixelLog, Link $link, Client $client): EventInterface
    {
        $this->pixelLog = $pixelLog;
        $this->link     = $link;
        $this->client   = $client;

        return $this;
    }

    /**
     * @return bool
     */
    abstract public function handle(): bool;
}
