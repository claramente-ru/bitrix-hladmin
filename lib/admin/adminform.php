<?php
declare(strict_types=1);

namespace Claramente\Hladmin\Admin;

use CAdminForm;
use Claramente\Hladmin\Services\HighloadRightService;
use Claramente\Hladmin\Services\HighloadService;
use Claramente\Hladmin\Structures\HlBlockStructure;
use CModule;

/**
 * Административные методы для модуля claramente.hladmin
 */
final class AdminForm
{
    /**
     * @var HighloadRightService
     */
    protected HighloadRightService $rightService;

    public function __construct()
    {
        $this->rightService = new HighloadRightService();
    }

    /**
     * Получить список секций для select поля
     * @return array
     */
    public function getSelectSections(bool $withEmpty = true): array
    {
        $result = [];
        if ($withEmpty) {
            $result[null] = 'Не выбрано';
        }
        $sections = (new HighloadService())->getSections();
        foreach ($sections as $section) {
            $result[$section->id] = $section->name;
        }

        return $result;
    }

    /**
     * Получить вкладки
     * @return array
     */
    public function getMainFormTabs(): array
    {
        $tabs = [];
        $tabs[] = $this->collectTab('📚 Справочники', 'hlblocks');
        // Вкладка доступов
        $tabs[] = $this->collectTab('🧑‍🧑‍🧒‍🧒️️ Права доступов', 'rights');
        // Вкладка для настроек tabs
        $tabs[] = $this->collectTab('🗂️ Секции', 'sections');
        // Вкладка о нас
        $tabs[] = $this->collectTab(name: 'ℹ️ О модуле', div: 'about', sort: 999_999_999);

        return $tabs;
    }

    /**
     * Экземпляр построения административной панели
     * @param string $name
     * @param array $tabs
     * @param bool $canExpand
     * @param bool $denyAutosave
     * @return CAdminForm
     */
    public function getForm(
        string $name = 'tabControl',
        array  $tabs = [],
        bool   $canExpand = true,
        bool   $denyAutosave = false
    ): CAdminForm
    {
        return new CAdminForm(
            $name,
            $tabs,
            $canExpand,
            $denyAutosave
        );
    }

    /**
     * Формирование Tab
     * @param string $name
     * @param string $div
     * @param int|null $id
     * @param int $sort
     * @param bool $required
     * @param string $icon
     * @param string|null $code
     * @return array @see CAdminForm
     */
    public function collectTab(
        string $name,
        string $div,
        ?int   $id = null,
        int    $sort = 100,
        bool   $required = true,
        string $icon = 'fileman',
        string $code = null
    ): array
    {
        return [
            'CODE' => $code,
            'ID' => $id,
            'SORT' => $sort,
            'TAB' => $name,
            'ICON' => $icon,
            'TITLE' => $name,
            'DIV' => $div,
            'required' => $required
        ];
    }

    /**
     * Добавление HTML формы редактирование HL
     * @param CAdminForm $form
     * @param HlBlockStructure $hlblock
     * @return void
     */
    public function setHlblockEditField(CAdminForm &$form, HlBlockStructure $hlblock): void
    {
        $sectionId = sprintf('hlblocks[%d]', $hlblock->id);
        // Начало блока ввода
        $form->BeginCustomField($sectionId, $hlblock->name);
        require __DIR__ . '/../../admin/pages/includes/highload_element_list.php';
        // Конец блока вывода
        $form->EndCustomField($sectionId);
    }

    /**
     * HTML форма прав доступа
     * @param CAdminForm $form
     * @param HlBlockStructure $hlblock
     * @return void
     */
    public function setHlblockRightField(CAdminForm &$form, HlBlockStructure $hlblock): void
    {
        $fieldId = sprintf('right[%d]', $hlblock->id);
        $form->AddViewField(
            $fieldId,
            '',
            '<a href="/bitrix/admin/claramente_hladmin.php?lang=' . LANG . '&page=rights&ID=' . $hlblock->id . '" style="text-decoration: none;font-weight: bold;">📚 ' . $hlblock->name . '</a>'
        );

        $hlRights = $this->rightService->getHighloadRights($hlblock);
        // Шаг 1: Выводим все возможные права
        foreach ($this->rightService->getHighloadRightTasks() as $task) {
            $fieldTaskId = sprintf('%s[%d]', $fieldId, $task['ID']);
            $taskIcon = match ($task['NAME']) {
                'hblock_write' => '✏️',
                'hblock_read' => '🔎',
                default => '🔒'
            };
            // Шаг 2: Собираем массив разрешенных правил для этой task
            $taskRights = [];
            foreach ($hlRights as $hlRight) {
                if ($hlRight->taskId != $task['ID']) {
                    continue;
                }
                $taskRights[] = [
                    'FIELD_ID' => sprintf('%s[%s]', $fieldTaskId, $hlRight->accessCode),
                    'TEXT' => sprintf('<span style="color: %s;">%s</span>', $this->getAccessCodeHexColor($hlRight->accessCode), $hlRight->groupTitle)
                ];
            }
            // Если отсутствуют права, запишем, что не заполнено
            if (! $taskRights) {
                $taskRights[] = [
                    'FIELD_ID' => sprintf('%s[%s]', $fieldTaskId, 'empty'),
                    'TEXT' => '<span style="color: #808080;">Не заполнено</span>'
                ];
            }

            // Шаг 3: Выводим название правила и первый элемент
            $firstTaskRight = current($taskRights);
            $form->AddViewField($fieldTaskId, $taskIcon . ' ' . $task['TITLE'] . ': ', $firstTaskRight['TEXT'], true);

            // Шаг 4: Проходимся по остальным правилам TASK
            if (count($taskRights) <= 1) {
                // Указан только один элемент, который мы вывели уже
                continue;
            }
            foreach (array_slice($taskRights, 1, count($taskRights) - 1) as $taskRight) {;
                $form->AddViewField($taskRight['FIELD_ID'], '', $taskRight['TEXT']);
            }
        }
    }

    /**
     * Получить поле для редактирования выпадающего списка
     * @param string $name
     * @param array $values
     * @param int|string|null $selected
     * @return string
     */
    private function getFieldSelect(string $name, array $values, int|string|null $selected = null): string
    {
        $html = '<select name="' . $name . '"';
        $html .= '>';

        foreach ($values as $key => $val) {
            $html .= '<option value="' . htmlspecialcharsbx($key) . '"' . ($selected == $key ? ' selected' : '') . '>' . htmlspecialcharsex($val) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * @param string $accessCode
     * @return string
     */
    private function getAccessCodeHexColor(string $accessCode): string
    {
        return match ($accessCode) {
            'G1' => '#F20F0F',
            'AU' => '#A60FF2',
            'CR' => '#F0B617',
            default => '#000'
        };
    }
}
