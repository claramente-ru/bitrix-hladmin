<?php

use Bitrix\Main\Request;
use Claramente\Hladmin\Services\HighloadService;
use Claramente\Hladmin\Admin\AdminForm;

/**
 * @var Request $request
 */

// Сохранение формы
if ($request->isPost()) {
    require_once __DIR__ . '/../save/main_save.php';
}

$hlblockService = new HighloadService();

// Главная страница модуля
$form = new AdminForm();
$tabControl = $form->getForm('claramente_hladmin_form', $form->getFormTabs());
$tabControl->SetShowSettings(false);
$tabControl->Begin([
    'FORM_ACTION' => $request->getRequestUri()
]);
// Проходимся по всем tabs
foreach ($form->getFormTabs() as $formTab) {
    $tabControl->BeginNextFormTab();

    // Вкладка - Справочники
    if ('hlblocks' === $formTab['DIV']) {
        /**
         * Для начала выведем секции, потом справочники которые относятся к секциям
         */
        foreach ($hlblockService->getSections() as $section) {
            // Заголовок секции
            $tabControl->AddSection('section-list-' . $section->id, $section->name);
            // Справочники
            foreach ($hlblockService->getHighloads() as $hlblock) {
                if ($hlblock->sectionStructure?->id === $section->id) {
                    // Форму редактирования справочника
                    $form->setHlblockEditField($tabControl, $hlblock);
                }
            }
        }
        // Справочники без секции
        $tabControl->AddSection('section-list-no-section', 'Без секции');
        // Справочники
        foreach ($hlblockService->getHighloads() as $hlblock) {
            if (null === $hlblock->sectionStructure) {
                // Форму редактирования справочника
                $form->setHlblockEditField($tabControl, $hlblock);
            }
        }
    }

    // Вкладка - Секции
    if ('sections' === $formTab['DIV']) {
        foreach ($hlblockService->getSections() as $section) {
            $div = sprintf('sections[%d]', $section->id);
            // Визуальное разделение
            $tabControl->AddSection('section-edit-' . $section->id, $section->name);

            $tabControl->AddEditField($div . '[name]', '📝 Заголовок', true, [], $section->name);
            $tabControl->AddEditField($div . '[code]', '🔤 Символьный код', true, [], $section->code);
            $tabControl->AddEditField($div . '[sort]', '🔝️ Сортировка', false, [], $section->sort);
            $tabControl->AddCheckBoxField($div . '[del]', '❌ Удалить', false, ['Y', 'N'], false);
        }
        // Добавить новую секцию
        $tabControl->AddSection('section-add', '📥 Добавить новую секцию');
        $tabControl->AddEditField('section_add[name]', '📝 Заголовок', false, [], '');
        $tabControl->AddEditField('section_add[code]', '🔤 Символьный код', false, [], '');
        $tabControl->AddEditField('section_add[sort]', '🔝️ Сортировка', false, [], 100);
    }
}

// Кнопка добавить новый справочник
$buttonAddNewParameter = '<a href="/bitrix/admin/highloadblock_entity_edit.php?lang=' . LANG . '"><input type="button" value="Добавить новый Highload-блок" title="Добавить новый Highload-блок" class="adm-btn-add"></a>';
$tabControl->Buttons(
    [
        'disabled' => false,
        'btnApply' => false,
    ],
    $buttonAddNewParameter
);

$tabControl->Show();