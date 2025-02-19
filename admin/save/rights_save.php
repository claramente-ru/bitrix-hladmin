<?php

global $USER;

use Bitrix\Highloadblock\HighloadBlockRightsTable;
use Bitrix\Main\Application;
use Claramente\Hladmin\Services\HighloadService;

if (! $USER->IsAdmin()) {
    return;
}

$request = Application::getInstance()->getContext()->getRequest();

// Текущий справочник
$hlService = new HighloadService();
$highload = $hlService->getHighloadById((int)$request->get('ID'));
if (! $highload) {
    CAdminMessage::ShowMessage('Справочник не найден');
    return;
}

/**
 * Обновление прав
 */
$rights = (array)$request->getPost('rights');
foreach ($rights as $accessCode => $taskId) {
    $taskId = (int)$taskId;

    $data = [
        'HL_ID' => $highload->id,
        'ACCESS_CODE' => $accessCode,
        'TASK_ID' => $taskId
    ];

    // Шаг 1: Получим текущие права
    $currentRight = HighloadBlockRightsTable::query()
        ->setSelect(['*'])
        ->setFilter([
            '=HL_ID' => $highload->id,
            '=ACCESS_CODE' => $accessCode
        ])
        ->fetch();
    // Если имеются текущие права, обновим или удалим их
    if ($currentRight) {
        // Если TASK_ID = 0, то удаляем правило
        if ($taskId === 0) {
            HighloadBlockRightsTable::delete($currentRight['ID']);
        } else {
            // Иначе обновим правило
            HighloadBlockRightsTable::update($currentRight['ID'], $data);
        }
    } elseif ($taskId > 0) {
        // Добавляем новое правило
        HighloadBlockRightsTable::add($data);
    }
}

// Редирект относительно нажатой кнопки
if ($request->getPost('apply')) {
    LocalRedirect('/bitrix/admin/claramente_hladmin.php?lang=' . LANG . '&page=rights&ID=' . $highload->id);
} else {
    // Редирект на страницу опций
    LocalRedirect('/bitrix/admin/claramente_hladmin.php?lang=' . LANG);
}
