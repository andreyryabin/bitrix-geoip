<?php

class GeoipItem
{
    private string $cityName    = '';
    private string $regionName  = '';
    private string $countryName = '';

    public function getCityName(): string
    {
        return $this->cityName;
    }

    public function setCityName(string $cityName): GeoipItem
    {
        $this->cityName = $cityName;
        return $this;
    }

    public function getRegionName(): string
    {
        return $this->regionName;
    }

    public function setRegionName(string $regionName): GeoipItem
    {
        $this->regionName = $regionName;
        return $this;
    }

    public function getCountryName(): string
    {
        return $this->countryName;
    }

    public function setCountryName(string $countryName): GeoipItem
    {
        $this->countryName = $countryName;
        return $this;
    }
}
