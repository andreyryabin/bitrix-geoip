<?php

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

/**
 * Провайдер непосредственно работающий с сервисом SypexGeo, через http-клиент
 * вся логика по получению данных из него только тут и нигде больше
 *
 * решил отдавать исключение если нужные поля не нашлись, и объект получился пустой
 * спорное решение, возможно стоит сохранять такие пустышки чтобы не гонять запросы к сервису
 */
class GeoipSypexProvider implements GeoipProviderInterface
{
    /**
     * @throws GeoipProviderItemNotFoundException
     */
    public function get(string $ip): GeoipItem
    {
        $httpClient = new HttpClient();

        $response = $httpClient->get(sprintf('https://api.sypexgeo.net/json/%s', $ip));

        try {
            $response = Json::decode($response);

            $geoipItem = (new GeoipItem)
                ->setCountryName($response['country']['name_ru'] ?? '')
                ->setCityName($response['city']['name_ru'] ?? '')
                ->setRegionName($response['region']['name_ru'] ?? '');

            $str = implode('', [
                $geoipItem->getCountryName(),
                $geoipItem->getRegionName(),
                $geoipItem->getCityName(),
            ]);

            if (!empty($str)) {
                return $geoipItem;
            }
        } catch (ArgumentException $e) {
        }

        throw new GeoipProviderItemNotFoundException();
    }
}
