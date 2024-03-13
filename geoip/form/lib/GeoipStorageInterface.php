<?php

/**
 * Интерфейс хранилища отвечает за то чтобы
 * 1) принять ip в виде строки и отдать объект с данными
 * 2) записать себе данные по ip
 *
 *  Хранилище должно работать с объектом GeoipItem который является универсальным для любых других хранилищ
 *  поддерживающих этот интерфейс
 */
interface GeoipStorageInterface
{
    public function get(string $ip): GeoipItem;

    public function set(string $ip, GeoipItem $item): int;
}
