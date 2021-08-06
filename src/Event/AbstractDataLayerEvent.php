<?php
declare(strict_types = 1);

namespace App\Event;

use App\Service\OrderService;
use App\Service\OrdersProductService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class AbstractDataLayerEvent
 */
abstract class AbstractDataLayerEvent extends AbstractEvent
{
    /**
     * @var string[]
     */
    protected $validationRules = [
        'products.*.id'       => 'required|string',
        'products.*.name'     => 'required|string',
        'products.*.price'    => 'required|numeric',
        'products.*.variant'  => 'nullable|string',
        'products.*.category' => 'nullable|string',
        'products.*.quantity' => 'nullable|numeric|min:1',
    ];

    /**
     * @var string
     */
    protected $purchaseType;

    /**
     * Обрабатывает заказ
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(): bool
    {
        foreach ($this->getEvents() as $event) {
            if (!$this->isPurchaseExist($event)) {
                continue;
            }

            $purchase = $event['ecommerce'][$this->purchaseType];

            $this->validatePurchase($purchase);

            $order = OrderService::getPurchaseOrder(
                $this->pixelLog,
                $this->link,
                $this->client,
                $this->getOrderId($purchase),
                $purchase
            );

            logger()->debug('Найдено продуктов: ' . count($purchase['products']));

            foreach ($purchase['products'] as $product_data) {
                $product = OrdersProductService::getProduct($this->pixelLog, $order, $product_data);

                logger()->debug('Сохранен продукт: ' . $product->product_name);
            }
        }

        // Возможно нужно оставить в foreach как в исходном задании
        $this->pixelLog->is_order = true;

        return true;
    }

    /**
     * @param array $purchase
     *
     * @return void
     * @throws ValidationException
     */
    private function validatePurchase(array $purchase): void
    {
        $validator = Validator::make($purchase, $this->validationRules);

        if ($validator->fails()) {
            logger()->debug('Ошибка валидации заказа');

            throw new ValidationException($validator);
        }
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getEvents(): array
    {
        $events = $this->pixelLog->data['dataLayer'];

        if (!is_array($events)) {
            throw new \Exception('dataLayer is not an array');
        }

        return $events;
    }

    /**
     * @param array $event
     *
     * @return bool
     */
    private function isPurchaseExist(array $event): bool
    {
        return isset($event['event'])
            && isset($event['ecommerce'])
            && isset($event['ecommerce'][$this->purchaseType]);
    }

    /**
     * @param array $source
     *
     * @return string
     */
    abstract protected function getOrderId(array $source): string;
}
