<?php
declare(strict_types = 1);

namespace App\Event;

use App\Models\PixelLog;
use App\Models\Client;
use App\Models\Link;

/**
 * Class PurchaseDataLayerEvent;
 */
class PurchaseDataLayerEvent extends AbstractDataLayerEvent
{
    /**
     * @var string
     */
    protected $purchaseType = 'purchase';

    /**
     * PurchaseDataLayerEvent constructor.
     */
    public function __construct()
    {
        // добавляем дополнительные правила валидации для этого типа заказа
        $this->validationRules = array_merge($this->validationRules, [
            'actionField.id'      => 'required|string',
            'actionField.action'  => 'nullable|string|in:purchase',
            'actionField.revenue' => 'required|numeric',
        ]);
    }

    /**
     * @param array $source
     *
     * @return string
     */
    protected function getOrderId(array $source): string
    {
        return $source['actionField']['id'];
    }
}
