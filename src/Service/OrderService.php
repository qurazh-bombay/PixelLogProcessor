<?php
declare(strict_types = 1);

namespace App\Service;

use App\Models\Order;
use App\Models\PixelLog;
use App\Models\Client;
use App\Models\Link;

/**
 * Class OrderService
 */
class OrderService
{
    /**
     * @param PixelLog $pixel_log
     * @param Link     $link
     * @param Client   $client
     * @param          $order_id
     *
     * @return Order
     */
    public static function getOrder(PixelLog $pixel_log, Link $link, Client $client, $order_id): Order
    {
        $order = self::getOrCreateOrder($pixel_log, $order_id);
        self::fillUpOrder($pixel_log, $link, $client, $order);

        return $order;
    }

    /**
     * @param PixelLog $pixel_log
     * @param Link     $link
     * @param Client   $client
     * @param          $order_id
     * @param array    $purchase
     *
     * @return Order
     */
    public static function getPurchaseOrder(
        PixelLog $pixel_log,
        Link     $link, Client $client,
                 $order_id,
        array    $purchase
    ): Order {
        $order = self::getOrCreateOrder($pixel_log, $order_id, true);
        self::fillUpOrder($pixel_log, $link, $client, $order, $purchase);

        return $order;
    }

    /**
     * @param PixelLog $pixel_log
     * @param mixed    $order_id
     * @param bool     $isWithLogger
     *
     * @return Order
     */
    private static function getOrCreateOrder(PixelLog $pixel_log, $order_id, bool $isWithLogger = false): Order
    {
        $order = Order::query()
            ->where('pp_id', '=', $pixel_log->pp_id)
            ->where('order_id', '=', $order_id)
            ->first();

        if (!$order) {
            if ($isWithLogger) {
                logger()->debug('Заказ №' . $order_id . ' не существует, создаем');
            }
            $order           = new Order();
            $order->pp_id    = $pixel_log->pp_id;
            $order->order_id = $order_id;
            $order->status   = 'new';
        } else {
            logger()->debug('Заказ №' . $order_id . ' существует, обновляем');
        }

        return $order;
    }

    /**
     * Заполняем заказ данными
     *
     * @param PixelLog   $pixel_log
     * @param Link       $link
     * @param Client     $client
     * @param Order      $order
     * @param array|null $purchase
     *
     * @return void
     */
    private function fillUpOrder(
        PixelLog $pixel_log,
        Link     $link, Client $client, Order $order,
        ?array   $purchase = null
    ): void {
        $order->pixel_id   = $pixel_log->id;
        $order->datetime   = $pixel_log->created_at;
        $order->partner_id = $link->partner_id;
        $order->link_id    = $link->id;
        $order->click_id   = $pixel_log->data['click_id'] ?? null;
        $order->web_id     = $pixel_log->data['utm_term'] ?? null;
        $order->offer_id   = $link->offer_id;
        $order->client_id  = $client->id;

        if ($purchase !== null) {
            $order->gross_amount = $order->gross_amount = self::getOrderGrossAmount($purchase);
            $order->cnt_products = count($purchase['products']);
        }

        $order->save();
    }

    /**
     * @param array $purchase
     *
     * @return float|int
     */
    private static function getOrderGrossAmount(array $purchase)
    {
        $grossAmount = 0;

        foreach ($purchase['products'] as $product_data) {
            $grossAmount += $product_data['price'] * ($product_data['quantity'] ?? 1);
        }

        return $grossAmount;
    }
}
