<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;

ini_set('zend.exception_ignore_args', 0);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

/** @global $APPLICATION CMain */
global $APPLICATION;

$APPLICATION->SetTitle('Highload настройки');
$request = Application::getInstance()->getContext()->getRequest();

if (! Loader::includeModule('claramente.hladmin')) {
    throw new Exception('Необходимо установить модуль claramente.hladmin');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

// Главная страница
require_once 'pages/main_page.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
