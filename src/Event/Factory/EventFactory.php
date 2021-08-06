<?php
declare(strict_types = 1);

namespace App\Event\Factory;

use App\Event\CheckoutDataLayerEvent;
use App\Event\EventInterface;
use App\Event\PurchaseDataLayerEvent;
use App\Event\PurchaseEvent;

/**
 * Class EventFactory
 *
 * Создает ивент для заказа
 */
class EventFactory
{
    /**
     * @param array $data
     *
     * @return EventInterface
     * @throws \Exception
     */
    public static function create(array $data): EventInterface
    {
        $type             = $data['ev'];
        $isEmptyDataLayer = empty($data['dataLayer']);
        $isEmptyOrderId   = empty($data['ed']['order_id']);

        if ($type === 'purchase' && !$isEmptyOrderId) {
            if (!$isEmptyDataLayer) {
                $event = new CheckoutDataLayerEvent();
                $event->debugMessage = 'Это ваще-заказ в 1 клик';

                return $event;
            }

            // pixel_event
            $event = new PurchaseEvent();
            $event->debugMessage = 'Это лид-заказ';

            return $event;
        }

        if ($type === 'pageload' && !$isEmptyDataLayer) {
            $event = new PurchaseDataLayerEvent();
            $event->debugMessage = 'Это ваще-заказ';

            return $event;
        }

        logger()->debug('Это странный заказ');

        dump($data);

        throw new \Exception('Странный формат заказа!');
    }
}
