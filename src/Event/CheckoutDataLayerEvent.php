<?php
declare(strict_types = 1);

namespace App\Event;

/**
 * Class CheckoutDataLayerEvent
 */
class CheckoutDataLayerEvent extends AbstractDataLayerEvent
{
    /**
     * @var string
     */
    protected $purchaseType = 'checkout';

    /**
     * @param array $source
     *
     * @return string
     */
    protected function getOrderId(array $source): string
    {
        return $this->pixelLog->data['ed']['order_id'];
    }
}
