<?php

global $USER;

use Bitrix\Main\Application;
use Claramente\Hladmin\Entity\ClaramenteHladminHlblocksTable;
use Claramente\Hladmin\Entity\ClaramenteHladminSectionsTable;
use Claramente\Hladmin\Services\HighloadService;

if (! $USER->IsAdmin()) {
    return;
}

$request = Application::getInstance()->getContext()->getRequest();

$hlService = new HighloadService();

/**
 * Добавление новой секции
 */
$sectionTab = $request->getPost('section_add');
if (! empty($sectionTab['name']) && ! empty($sectionTab['code'])) {
    // Проверяем занятость кода
    if (ClaramenteHladminSectionsTable::getByCode($sectionTab['code'])) {
        CAdminMessage::ShowMessage('Ошибка. Секция с таким кодом уже существует');
        return;
    }
    // Добавляем новую вкладку
    $tab = ClaramenteHladminSectionsTable::add(
        [
            'NAME' => $sectionTab['name'],
            'SORT' => intval($sectionTab['sort'] ?? 0) ?: 100,
            'CODE' => $sectionTab['code']
        ]
    );
    if (! $tab->isSuccess()) {
        CAdminMessage::ShowMessage('Ошибка. ' . implode(',', $tab->getErrorMessages()));
        return;
    }
}

/**
 * Обработка секций
 */
$sections = $request->getPost('sections');
if (is_array($sections) && $sections) {
    foreach ($sections as $sectionId => $section) {
        // Необходимо удалить вкладку
        if (isset($section['del']) && $section['del'] === 'Y') {
            ClaramenteHladminSectionsTable::delete($sectionId);
            continue;
        }

        // Ошибка. Код секции пустой
        if (empty($section['code'])) {
            CAdminMessage::ShowMessage('Ошибка. Не указан код секции %s' . $section['name']);
            continue;
        }

        // Проверка уникальности кода секции
        $checkSection = ClaramenteHladminSectionsTable::getByCode($section['code']);
        $currentSection = ClaramenteHladminSectionsTable::getTabById((int)$sectionId);
        if ($currentSection->code != $section['code'] && null !== $currentSection) {
            CAdminMessage::ShowMessage('Ошибка. Секция с таким кодом уже существует');
        }

        // Обновим секцию
        $updateTab = ClaramenteHladminSectionsTable::update(
            $sectionId,
            [
                'NAME' => $section['name'] ?? 'Без измени',
                'SORT' => intval($section['sort'] ?? 100) ?: 100,
                'CODE' => $section['code']
            ]
        );
    }
}

/**
 * Обновление справочников
 */
$hlblocks = (array)$request->getPost('hlblocks');
foreach ($hlblocks as $hlblockId => $hlblockData) {
    // Заберем справочник по идентификатору
    $hlblock = $hlService->getHighloadById(intval($hlblockId));
    if (! $hlblock) {
        CAdminMessage::ShowMessage(sprintf('Ошибка. Справочник %d не найден', $hlblockId));
        return;
    }
    $sectionId = intval($hlblockData['section']) ?: null;
    $sort = intval($hlblockData['sort']) ?: 100;
    // Сохраним изменения справочника в модуль claramente.hladmin
    $hladmin = ClaramenteHladminHlblocksTable::getByHlblockId(intval($hlblockId));
    // Новый элемент
    if (null === $hladmin) {
        ClaramenteHladminHlblocksTable::add([
            'SECTION_ID' => $sectionId,
            'SORT' => $sort,
            'HLBLOCK_ID' => $hlblockId,
        ]);
    } else {
        // Обновим элемент
        ClaramenteHladminHlblocksTable::update(
            $hladmin['ID'],
            [
                'SECTION_ID' => $sectionId,
                'SORT' => $sort,
            ]
        );
    }
}

// Редирект на страницу модуля
LocalRedirect('/bitrix/admin/claramente_hladmin.php?lang=' . LANG);