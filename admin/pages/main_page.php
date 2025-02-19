<?php

use Bitrix\Main\Request;
use Claramente\Hladmin\Services\HighloadRightService;
use Claramente\Hladmin\Services\HighloadService;
use Claramente\Hladmin\Admin\AdminForm;

/**
 * @var Request $request
 * @var CUser $USER
 */

// Сохранение формы
if ($request->isPost()) {
    require_once __DIR__ . '/../save/main_save.php';
}

// Справочник
$hlblockService = new HighloadService();
// Права справочника
$hlRightService = new HighloadRightService();

// Главная страница модуля
$form = new AdminForm();
$tabControl = $form->getForm('claramente_hladmin_form', $form->getMainFormTabs());
$tabControl->SetShowSettings(false);
$tabControl->Begin([
    'FORM_ACTION' => $request->getRequestUri()
]);
// Проходимся по всем tabs
foreach ($form->getMainFormTabs() as $formTab) {
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
                    // Проверка доступности справочника
                    if (! $hlRightService->checkPermission($hlblock->id)) {
                        // Отсутствуют права на чтение
                        continue;
                    }
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
                // Проверка доступности справочника
                if (! $hlRightService->checkPermission($hlblock->id)) {
                    // Отсутствуют права на чтение
                    continue;
                }
                // Форму редактирования справочника
                $form->setHlblockEditField($tabControl, $hlblock);
            }
        }
    }

    // Вкладка - Секции
    if ('sections' === $formTab['DIV']  && $USER->IsAdmin()) {
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

    // Вкладка права доступа
    if ('rights' === $formTab['DIV'] && $USER->IsAdmin()) {
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
                    $form->setHlblockRightField($tabControl, $hlblock);
                }
            }
        }
        // Справочники без секции
        $tabControl->AddSection('section-list-no-section', 'Без секции');
        // Справочники
        foreach ($hlblockService->getHighloads() as $hlblock) {
            if (null === $hlblock->sectionStructure) {
                // Форму редактирования справочника
                $form->setHlblockRightField($tabControl, $hlblock);
            }
        }
    }

    // Вкладка о нас
    if ('about' === $formTab['DIV']) {
        $tabControl->AddViewField(
            'about-license',
            '⚖️ Лицензия',
            '<a target="_blank" href="https://github.com/claramente-ru/bitrix-hladmin/blob/master/LICENSE">MIT</a>'
        );
        $tabControl->AddViewField(
            'about-git',
            '𝗚𝐈𝗧️ GitHub',
            '<a target="_blank" href="https://github.com/claramente-ru/bitrix-hladmin">https://github.com/claramente-ru/bitrix-hladmin</a>'
        );
        $tabControl->AddViewField(
            'about-packagist',
            '🐘️ Packagist',
            '<a target="_blank" href="https://packagist.org/packages/claramente/claramente.hladmin">https://packagist.org/packages/claramente/claramente.hladmin</a>'
        );
        $tabControl->AddViewField(
            'about-developer',
            '⚒️ Разработчик',
            '<a target="_blank" href="https://claramente.ru">© Светлые головы</a>'
        );
    }
}

// Кнопка добавить новый справочник
$buttonAddNewHighload = '';
if ($USER->IsAdmin()) {
    $buttonAddNewHighload = '<a href="/bitrix/admin/highloadblock_entity_edit.php?lang=' . LANG . '"><input type="button" value="Добавить новый Highload-блок" title="Добавить новый Highload-блок" class="adm-btn-add"></a>';
}
$tabControl->Buttons(
    [
        'disabled' => false,
        'btnApply' => false,
    ],
    $buttonAddNewHighload
);

$tabControl->Show();