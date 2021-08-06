<?php
declare(strict_types = 1);

namespace App\Processors;

use App\Event\Factory\EventFactory;
use App\Service\ClickService;
use App\Service\ClientService;
use App\Service\LinkService;
use App\Models\Click;
use App\Models\Client;
use App\Models\Link;
use App\Models\PixelLog;
use Illuminate\Validation\ValidationException;

/**
 * Class PixelLogProcessor
 */
class PixelLogProcessor
{
    /**
     * @var PixelLog
     */
    private $pixel_log;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Link
     */
    private $link;

    /**
     * PixelLogProcessor constructor.
     *
     * @param PixelLog $pixel_log
     */
    public function __construct(PixelLog $pixel_log)
    {
        $this->pixel_log = $pixel_log;
    }

    /**
     * @return void
     */
    public function process(): void
    {
        try {
            // Получаем новое значение для поля is_valid
            // Валидация записи. В случае ошибки - выдаст Exception
            $this->pixel_log->is_valid = $this->pixel_log->isDataValid();

            $this->client = $this->getClient();
            $this->link   = $this->getLink();

            $this->handleClick();
            $this->handlePurchase();

            $this->pixel_log->status = null;
        } catch (ValidationException $e) {
            $this->pixel_log->status = json_encode($e->errors(), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE);
            logger()->warning('e', $e->errors());
        } catch (\Throwable $e) {
            $this->pixel_log->status = $e->getMessage();
            logger()->warning($e->getMessage());
            logger('Пойман Exception в pixel_log #' . $this->pixel_log->id, [$e]);
            dump($e);
        } finally {
            $this->pixel_log->save();
        }
    }

    /**
     * Проверяем, существует ли такой clientId
     * Если нет - создаем
     *
     * @return Client
     * @throws \Exception
     */
    private function getClient(): Client
    {
        if (empty($this->pixel_log->data['uid'])) {
            throw new \Exception('Пустой uid');
        }

        return ClientService::getClient($this->pixel_log->data);
    }

    /**
     * @return Link
     * @throws \Exception
     */
    private function getLink(): Link
    {
        $link = LinkService::getLink($this->pixel_log);

        if ($link === null) {
            $msg = sprintf(
                'Не найден линк #%s у партнера #%s',
                $this->pixel_log->data['utm_campaign'],
                $this->pixel_log->data['utm_content']
            );

            throw new \Exception($msg);
        }

        return $link;
    }

    /**
     * Обработка клика
     *
     * @return void
     */
    private function handleClick(): void
    {
        $this->pixel_log->is_click = false;

        if (!$this->pixel_log->isClick()) {
            // Это не клик, пропускаем
            return;
        }

        $this->pixel_log->is_click = ClickService::saveClick($this->pixel_log, $this->link, $this->client);
    }

    /**
     * Обработка покупки
     *
     * @return bool
     * @throws \Exception
     */
    private function handlePurchase(): bool
    {
        $this->pixel_log->is_order = false;

        if (!$this->pixel_log->isPurchase()) {
            logger()->debug('Не является заказом');

            return false;
        }

        $event = EventFactory::create($this->pixel_log->data);

        logger()->debug($event->debugMessage);

        return $event->setConfig($this->pixel_log, $this->link, $this->client)->handle();
    }
}
