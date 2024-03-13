<?php

use Bitrix\Main\UI\Extension;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain                 $APPLICATION
 * @var array                    $arResult
 * @var CBitrixComponentTemplate $this
 * @var array                    $arParams
 */

Extension::load(['ajax', 'ui.forms', 'ui.buttons']);
?>
<div class="geoip-component">
    <div style="display: flex">
        <div class="ui-ctl ui-ctl-textbox ui-ctl-before-icon ui-ctl-after-icon">
            <div class="ui-ctl-before ui-ctl-icon-search"></div>
            <input class="geoip-text ui-ctl-element" type="text"/>
        </div>
        <button class="geoip-btn ui-btn ui-btn-primary">Найти</button>
    </div>
    <div class="geoip-result"></div>
</div>


