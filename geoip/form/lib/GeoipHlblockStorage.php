<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

Loader::includeModule("highloadblock");

/**
 *  Хранилище непосредственно работающее с Highload-блоками, вся логика по хранению
 * и получению данных только тут и нигде больше
 */
class GeoipHlblockStorage implements GeoipStorageInterface
{
    const TABLE_NAME = 'b_geoip_storage';
    const NAME       = 'GeoipStorage';
    private DataManager $dataManager;

    public function __construct()
    {
        if (!$this->isGeoipHlblockExists()) {
            $hlblockId = $this->createGeoipHlblock();

            $this->createFieldIp($hlblockId);
            $this->createFieldResponse($hlblockId);
        }

        $this->createDataManager();
    }

    /**
     * @throws GeoipStorageItemNotFoundException
     */
    public function get(string $ip): GeoipItem
    {
        $dataManager = $this->getDataManager();

        try {
            $record = $dataManager::getList([
                'filter' => [
                    'UF_IP' => $ip,
                ],
                'limit'  => 1,
            ])->fetch();
        } catch (\Exception $e) {
        }

        if (empty($record)) {
            throw new GeoipStorageItemNotFoundException;
        }

        try {
            $response = Json::decode($record['UF_RESPONSE']);
        } catch (ArgumentException $e) {
        }

        return (new GeoipItem)
            ->setCountryName($response['country_name'] ?? '')
            ->setCityName($response['city_name'] ?? '')
            ->setRegionName($response['region_name'] ?? '');
    }

    public function set(string $ip, GeoipItem $item): int
    {
        $response = Json::encode([
            'country_name' => $item->getCountryName(),
            'city_name'    => $item->getCityName(),
            'region_name'  => $item->getRegionName(),
        ]);

        $dataManager = $this->getDataManager();

        $result = $dataManager::add([
            'UF_IP'       => $ip,
            'UF_RESPONSE' => $response,
        ]);

        return $result->getId();
    }

    protected function isGeoipHlblockExists(): bool
    {
        return is_array($this->getGeoipHlblock());
    }

    protected function getGeoipHlblock()
    {
        return HighloadBlockTable::getList(
            [
                "filter" => [
                    'NAME' => self::NAME,
                ],
            ]
        )->fetch();
    }

    protected function createGeoipHlblock(): int
    {
        $res = HighloadBlockTable::add([
            'TABLE_NAME' => self::TABLE_NAME,
            'NAME'       => self::NAME,
        ]);

        return $res->getId();
    }

    protected function createFieldIp(int $hlblockId)
    {
        $entityId = HighloadBlockTable::compileEntityId($hlblockId);

        (new CUserTypeEntity)->Add([
            'ENTITY_ID'     => $entityId,
            'FIELD_NAME'    => 'UF_IP',
            'USER_TYPE_ID'  => 'string',
            'SORT'          => '200',
            'MULTIPLE'      => 'N',
            'MANDATORY'     => 'Y',
            'SHOW_FILTER'   => 'Y',
            'SHOW_IN_LIST'  => 'Y',
            'EDIT_IN_LIST'  => 'Y',
            'IS_SEARCHABLE' => 'Y',
            'SETTINGS'      =>
                [
                    'SIZE'          => 50,
                    'ROWS'          => 1,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 50,
                    'DEFAULT_VALUE' => '',
                ],
        ]);
    }

    protected function createFieldResponse(int $hlblockId)
    {
        $entityId = HighloadBlockTable::compileEntityId($hlblockId);

        (new CUserTypeEntity)->Add([
            'ENTITY_ID'     => $entityId,
            'FIELD_NAME'    => 'UF_RESPONSE',
            'USER_TYPE_ID'  => 'string',
            'SORT'          => '250',
            'MULTIPLE'      => 'N',
            'MANDATORY'     => 'N',
            'SHOW_FILTER'   => 'N',
            'SHOW_IN_LIST'  => 'Y',
            'EDIT_IN_LIST'  => 'Y',
            'IS_SEARCHABLE' => 'N',
            'SETTINGS'      =>
                [
                    'SIZE'          => 80,
                    'ROWS'          => 10,
                    'REGEXP'        => '',
                    'MIN_LENGTH'    => 0,
                    'MAX_LENGTH'    => 0,
                    'DEFAULT_VALUE' => '',
                ],
        ]);
    }

    protected function getDataManager(): DataManager
    {
        return $this->dataManager;
    }

    protected function createDataManager()
    {
        $dataManager = HighloadBlockTable::compileEntity(
            $this->getGeoipHlblock()
        )->getDataClass();

        if (is_object($dataManager)) {
            $this->dataManager = $dataManager;
        } else {
            $this->dataManager = new $dataManager;
        }
    }
}
