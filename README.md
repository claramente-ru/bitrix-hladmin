# Модуль Highload-блоков для Bitrix

[![Claramente](https://claramente.ru/upload/claramente/a2c/ho3rj4p3j2t7scsartohgjajkb1xkyh0/logo.svg)](https://claramente.ru)

Установка через composer
-------------------------
Пример composer.json с установкой модуля в local/modules/
```
{
  "extra": {
    "installer-paths": {
      "local/modules/{$name}/": ["type:bitrix-module"]
    }
  },
  "require": {
    "claramente/claramente.hladmin": "dev-master"
  }
}
```

1. Запустить `composer require claramente/claramente.hladmin dev-master`

2. В административном разделе установить модуль **claramente.hladmin** _(/bitrix/admin/partner_modules.php?lang=ru)_

3. После установки модуля он будет доступен в разделе Highload-блоки

О модуле
-------------------------
Этот модуль позволяет переназначить управление и вывод Highload-справочников в административном разделе.
Доступна удобная сортировка и разбивка справочников по секциям.

## Странице настроек справочников
![](https://claramente.ru/upload/claramente/claramente-hladmin-main-page.png)