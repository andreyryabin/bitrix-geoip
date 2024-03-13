<?php

//тут должна быть нормальная автозагрузка классов

require_once __DIR__ . '/lib/GeoipProviderItemNotFoundException.php';
require_once __DIR__ . '/lib/GeoipStorageItemNotFoundException.php';
require_once __DIR__ . '/lib/GeoipInvalidIpException.php';
require_once __DIR__ . '/lib/GeoipItem.php';
require_once __DIR__ . '/lib/GeoipStorageInterface.php';
require_once __DIR__ . '/lib/GeoipProviderInterface.php';
require_once __DIR__ . '/lib/GeoipHlblockStorage.php';
require_once __DIR__ . '/lib/GeoipSypexProvider.php';

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;

class GeoipComponent extends CBitrixComponent implements
    Controllerable,
    Errorable
{
    /** @var ErrorCollection */
    protected $errorCollection;

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();

        return $arParams;
    }

    /**
     * Метод вызывается через асинхронно через
     * BX.ajax.runComponentAction('geoip:form', 'check',...);
     *
     *
     * @param $checkIp
     *
     * @return string
     */
    public function checkAction($checkIp = '')
    {
        try {
            $geoipItem = $this->checkIp($checkIp);

            return sprintf(
                'Страна: %s, Регион: %s, Город: %s',
                $geoipItem->getCountryName(),
                $geoipItem->getRegionName(),
                $geoipItem->getCityName(),
            );
        } catch (GeoipInvalidIpException $e) {
            $this->errorCollection[] = new Error(sprintf('IP: "%s" указан некорректно', $checkIp));
        } catch (GeoipProviderItemNotFoundException $e) {
            $this->errorCollection[] = new Error(sprintf('IP: "%s" не найден', $checkIp));
        }

        $this->sendDebugEmail();

        return "";
    }

    /**
     * @throws GeoipProviderItemNotFoundException
     * @throws GeoipInvalidIpException
     */
    protected function checkIp($currentIp): GeoipItem
    {
        //выбор провайдера (Sypex) и хранилища данных (Highload-блоки)

        $storage = new GeoipHlblockStorage();
        $provider = new GeoipSypexProvider();

        if (filter_var($currentIp, FILTER_VALIDATE_IP) === false) {
            //невалидный емейл
            throw new GeoipInvalidIpException();
        }

        try {
            //ищем ip в хранилище
            $geoipItem = $storage->get($currentIp);
        } catch (GeoipStorageItemNotFoundException $e) {
            //ищем ip у провайдера
            $geoipItem = $provider->get($currentIp);

            //записываем ответ в хранилище
            $storage->set($currentIp, $geoipItem);
        }

        return $geoipItem;
    }

    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    /**
     * сбрасываем фильтры авторизации
     *
     * @return array[]
     */
    public function configureActions()
    {
        return [
            'check' => [
                'prefilters'  => [],
                'postfilters' => [],
            ],
        ];
    }

    /**
     * Отправка на емейл ошибок, как требовалось в тз
     * выдумывать ничего не стал, обычный bxmail
     *
     * для отладки таким способом в компонент надо передать параметр
     * DEBUG_EMAIL с необходимым емейлом
     *
     */
    protected function sendDebugEmail()
    {
        if (empty($this->arParams['DEBUG_EMAIL'])) {
            return;
        }

        $messages = [];
        /** @var Error $error */
        foreach ($this->errorCollection as $error) {
            $messages[] = $error->getMessage();
        }

        if (!empty($messages)) {
            bxmail($this->arParams['DEBUG_EMAIL'], 'Ошибки geoip', implode(PHP_EOL, $messages));
        }
    }
}
