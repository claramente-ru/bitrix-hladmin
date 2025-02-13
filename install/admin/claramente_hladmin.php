<?php

if (is_file($_SERVER['DOCUMENT_ROOT'] . '/local/modules/claramente.hladmin/admin/index.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/modules/claramente.hladmin/admin/index.php';
} else {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/claramente.hladmin/admin/index.php';
}
