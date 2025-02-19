<?php
declare(strict_types=1);

use Claramente\Hladmin\Services\HighloadRightService;
use Claramente\Hladmin\Services\HighloadService;
use Claramente\Hladmin\Structures\HlBlockStructure;

/**
 * Административные методы для модуля claramente.hladmin
 */
final class ClaramenteModuleHlAdmin
{
    /**
     * Отображение настроек в глобальном меню сайта
     * @param array $adminMenu
     * @param array $moduleMenu
     * @return void
     */
    public static function onBuildGlobalMenu(array &$adminMenu, array &$moduleMenu): void
    {
        $hlRightsService = new HighloadRightService();
        $hlblockService = new HighloadService();
        // Обработаем список справочников в highloadblock секции
        foreach ($moduleMenu as &$moduleMenu) {
            if ($moduleMenu['section'] !== 'highloadblock') {
                continue;
            }
            $moduleMenu['url'] = 'claramente_hladmin.php?lang=' . LANGUAGE_ID;
            $moduleMenu['more_url'][] = $moduleMenu['url'];
            // Очищаем старый items
            $moduleMenu['items'] = [];
            // Проходимся по списку секций
            foreach ($hlblockService->getSections() as $section) {
                $itemsId = $moduleMenu['items_id'] . '/' . $section->id;
                $data = [
                    'icon' => 'iblock_menu_icon_types',
                    'page_icon' => 'iblock_menu_icon_types',
                    'items_id' => $itemsId,
                    'dynamic' => true,
                    'more_url' => [],
                    'module_id' => 'highloadblock',
                    'text' => $section->name,
                    'items' => []
                ];
                // Справочники секции
                foreach ($hlblockService->getHighloads() as $hlblock) {
                    if ($hlblock->sectionStructure?->id === $section->id) {
                        // Проверка доступности справочника
                        if (! $hlRightsService->checkPermission($hlblock->id)) {
                            // Отсутствуют права на чтение
                            continue;
                        }
                        // Добавим справочник в секцию
                        $hlblockElement = self::getHlblockMenuItem($hlblock);
                        $data['items'][] = $hlblockElement;
                        // Так же добавим новый url для секции выше
                        $data['more_url'][] = $hlblockElement['url'];
                        $data['more_url'] = array_unique(
                            array_merge($data['more_url'], $hlblockElement['more_url'])
                        );
                    }
                }
                // Если отсутствуют элементы, не выводим
                if (! $data['items']) {
                    continue;
                }
                // Добавим собранные справочники
                $moduleMenu['items'][] = $data;
            }
            // Справочники без секции
            foreach ($hlblockService->getHighloads() as $hlblock) {
                if (null == $hlblock->sectionStructure) {
                    // Проверка доступности справочника
                    if (! $hlRightsService->checkPermission($hlblock->id)) {
                        // Отсутствуют права на чтение
                        continue;
                    }
                    // Добавим справочник в секцию
                    $moduleMenu['items'][] = self::getHlblockMenuItem($hlblock);
                }
            }
        }
        unset($moduleMenu);
    }

    /**
     * Получить элемент справочника для меню
     * @param HlBlockStructure $hlblock
     * @return array
     */
    private static function getHlblockMenuItem(HlBlockStructure $hlblock): array
    {
        return [
            'text' => $hlblock->name,
            'icon' => 'iblock_menu_icon_iblocks',
            'page_icon' => 'iblock_menu_icon_iblocks',
            'dynamic' => false,
            'url' => 'highloadblock_rows_list.php?ENTITY_ID=' . $hlblock->id . '&lang=' . LANGUAGE_ID,
            'module_id' => 'highloadblock',
            'more_url' => [
                'highloadblock_row_edit.php?ENTITY_ID=' . $hlblock->id,
                'highloadblock_entity_edit.php?ID=' . $hlblock->id,
            ]
        ];
    }
}

