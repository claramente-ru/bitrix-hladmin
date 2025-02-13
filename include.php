<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    'claramente.hladmin',
    [
        'ClaramenteModuleHlAdmin' => 'classes/general/claramentemodulehladmin.php',
    ]
);
