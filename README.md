## Форма GeoIP поиска

Компонент надо разместить по пути bitrix/components/geoip/form

Подключение компонента
```
<?php $APPLICATION->IncludeComponent("geoip:form", ".default", [],    false ); ?>

```

Компонент при первом запуске сам создаст highload-блок GeoipStorage (таблица b_geoip_storage)


