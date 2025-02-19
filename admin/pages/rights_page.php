<?php

use Bitrix\Main\Request;
use Claramente\Hladmin\Services\HighloadRightService;
use Claramente\Hladmin\Services\HighloadService;
use Claramente\Hladmin\Admin\AdminForm;

/**
 * @var Request $request
 * @var CUser $USER
 */

// Страница доступна только администраторам
if (! $USER->IsAdmin()) {
    CAdminMessage::ShowMessage('Страница доступна только администраторам');
    return;
}

// Сохранение формы
if ($request->isPost()) {
    require_once __DIR__ . '/../save/rights_save.php';
}

// Текущий справочник
$hlService = new HighloadService();
$highload = $hlService->getHighloadById((int)$request->get('ID'));
if (! $highload) {
    CAdminMessage::ShowMessage('Справочник не найден');
    return;
}
// Права справочника
$hlRightService = new HighloadRightService();
$rights = $hlRightService->getHighloadRights($highload);

// Доступные права справочника для select
$selectRights = $hlRightService->getHighloadRightTasks();
$selectRights = array_combine(
    array_column($selectRights, 'ID'),
    array_column($selectRights, 'TITLE'),
);
// Добавим "Не установлено" в самое начало списка
$selectRights = [0 => 'Не установлено'] + $selectRights;

// Главная страница модуля
$form = new AdminForm();
$tabControl = $form->getForm(
    name: 'claramente_hladmin_rights_form',
    tabs: [
        $form->collectTab('Права доступа справочника "' . htmlspecialcharsbx($highload->name) . '"', 'rights-edit')
    ],
    canExpand: false,
    denyAutosave: true
);
$tabControl->SetShowSettings(false);
$tabControl->Begin([
    'FORM_ACTION' => $request->getRequestUri()
]);
$tabControl->BeginNextFormTab();
$tabControl->AddSection('title', 'Регулярные настройки доступов');

// Отобразим все доступные права справочника
foreach ($rights as $right) {
    $tabControl->AddDropDownField(
        sprintf('rights[%s]', $right->accessCode),
        $right->groupTitle,
        false,
        $selectRights,
        $right->taskId
    );
}

// Кнопка дополнительные редактирования прав
$tabControl->AddViewField(
    'additional-rights',
    'Права для пользователей',
    '<a href="/bitrix/admin/highloadblock_entity_edit.php?ID=' . $highload->id . '&lang=' . LANG . '">Редактировать</a>'
);

// Кнопка отменить
$buttonCancel = '<a href="/bitrix/admin/claramente_hladmin.php?lang=' . LANG. '"><input type="button" value="Отменить" title="Отменить" class="adm-btn-cancel"></a>';
$tabControl->Buttons(
    [
        'disabled' => false,
        'btnApply' => true,
    ],
    $buttonCancel
);

$tabControl->Show();