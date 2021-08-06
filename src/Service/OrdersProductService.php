<?php
declare(strict_types = 1);

namespace App\Service;

use App\Models\OrdersProduct;
use App\Models\PixelLog;

/**
 * Class OrdersProductService
 */
class OrdersProductService
{
    /**
     * @param PixelLog $pixel_log
     * @param Order    $order
     * @param array    $product_data
     * @param bool     $isWithParent
     *
     * @return OrdersProduct
     */
    public static function getProduct(
        PixelLog $pixel_log,
        Order    $order,
        array    $product_data,
        bool     $isWithParent = false
    ): OrdersProduct {
        $product = self::createProduct($pixel_log, $order, $product_data);

        if ($isWithParent) {
            $product->parent_id = Order::query()
                ->where('pp_id', '=', $pixel_log->pp_id)
                ->where('order_id', '=', $order->order_id)
                ->first()->id;
        }

        $product->save();

        return $product;
    }

    /**
     * @param PixelLog $pixel_log
     * @param Order    $order
     * @param array    $product_data
     *
     * @return OrdersProduct
     */
    private static function createProduct(PixelLog $pixel_log, Order $order, array $product_data): OrdersProduct
    {
        $product_id = $product_data['id'];
        $product    = OrdersProduct::query()
                ->where('pp_id', '=', $pixel_log->pp_id)
                ->where('order_id', '=', $order->order_id)
                ->where('product_id', '=', $product_id)
                ->first() ?? new OrdersProduct();

        $product->pp_id         = $pixel_log->pp_id;
        $product->order_id      = $order->order_id;
        $product->datetime      = $order->datetime;
        $product->partner_id    = $order->partner_id;
        $product->offer_id      = $order->offer_id;
        $product->link_id       = $order->link_id;
        $product->product_id    = $product_id;
        $product->product_name  = trim(($product_data['name'] ?? '') . ' ' . ($product_data['variant'] ?? ''));
        $product->category      = $product_data['category'] ?? null;
        $product->price         = $product_data['price'];
        $product->quantity      = $product_data['quantity'] ?? 1;
        $product->total         = $product->price * $product->quantity;
        $product->web_id        = $order->web_id;
        $product->click_id      = $order->click_id;
        $product->pixel_id      = $order->pixel_id;
        $product->amount        = 0;
        $product->amount_advert = 0;
        $product->fee_advert    = 0;

        return $product;
    }
}
