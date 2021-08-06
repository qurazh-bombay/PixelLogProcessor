<?php
declare(strict_types = 1);

namespace App\Event;

use App\Service\OrderService;

/**
 * Class PurchaseEvent
 */
class PurchaseEvent extends AbstractEvent
{
    /**
     * Обрабатывает заказ
     *
     * @return bool
     */
    public function handle(): bool
    {
        $order_id = $this->pixelLog->data['ed']['order_id'];

        OrderService::getOrder($this->pixelLog, $this->link, $this->client, $order_id);

        $this->pixelLog->is_order = true;

        logger()->debug('Это продажа');

        return true;
    }
}
