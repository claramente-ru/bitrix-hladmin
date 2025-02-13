<?php
declare(strict_types=1);

namespace Claramente\Hladmin\Admin;

use CAdminForm;
use Claramente\Hladmin\Services\HighloadService;
use Claramente\Hladmin\Structures\HlBlockStructure;
use CModule;

/**
 * Административные методы для модуля claramente.hladmin
 */
final class AdminForm
{
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
    public function getFormTabs(): array
    {
        $tabs = [];
        $tabs[] = $this->collectTab('📚 Справочники', 'hlblocks');
        // Tab для настроек tabs
        $tabs[] = $this->collectTab('🗂️ Секции', 'sections');

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
        // Шапка
        echo '<tr id="tr_hlblocks[' . $sectionId . '][value]">
        <td class="adm-detail-content-cell-l">' . htmlspecialcharsbx($hlblock->name ?: $hlblock->code);
        // Список элементов
        echo '&ensp;| <a href="/bitrix/admin/highloadblock_rows_list.php?ENTITY_ID=' . $hlblock->id . '&lang=' . LANG_ADMIN_LID . '" title="Список элементов" style="text-decoration: none">📋 Элементы</a>';
        // Редактировать
        echo '&ensp;| <a href="/bitrix/admin/highloadblock_entity_edit.php?ID=' . $hlblock->id . '&lang=' . LANG_ADMIN_LID . '" title="Редактировать" style="text-decoration: none">✏️️ Изменить</a>';
        // Список полей
        echo '&ensp;| <a href="/bitrix/admin/userfield_admin.php?find_type=ENTITY_ID&set_filter=Y&find=HLBLOCK_' . $hlblock->id . '&lang=' . LANG_ADMIN_LID . '" title="Список полей" style="text-decoration: none">🛠️️️ Поля</a>';
        // Миграция справочника
        if (CModule::IncludeModule('sprint.migration')) {
            echo '&ensp;| <a href="/bitrix/admin/sprint_migrations.php?config=cfg" title="Миграций" style="text-decoration: none">💾 Миграция</a>';
        }
        echo '</td>';
        // Выпадающий список секций
        echo '<td class="adm-detail-content-cell-r" style="float: left;margin-left: 10px;">Секция: ';
        echo $this->getFieldSelect(
            name: $sectionId . '[section]',
            values: $this->getSelectSections(),
            selected: $hlblock->sectionStructure?->id
        );
        echo '</td>';
        // Сортировка поля
        echo '<td class="adm-detail-content-cell-r" style="float: left;margin-left: 10px;">Сортировка: <input type="text" name="' . $sectionId . '[sort]" size="5" value="' . $hlblock->sort . '"></td>';
        // Подвал
        echo '</tr>';
        $form->EndCustomField($sectionId);
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
}
