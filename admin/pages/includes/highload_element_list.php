<?php

use Claramente\Hladmin\Structures\HlBlockStructure;

/**
 * @var string $sectionId
 * @var HlBlockStructure $hlblock
 */
global $USER;
$buttonId = 'edit_button_' . $sectionId;
?>
<tr id="tr_hlblocks[<?= $sectionId ?>][value]">
    <td class="adm-detail-content-cell-l">
        <?= htmlspecialcharsbx($hlblock->name ?: $hlblock->code) ?>
        &ensp; | 🆔 <?= $hlblock->id ?>
        &ensp; | 🔤 <?= $hlblock->code ?>
        <!-- Список элементов -->
        &ensp; | <a href="/bitrix/admin/highloadblock_rows_list.php?ENTITY_ID=<?= $hlblock->id ?>&lang=<?= LANG_ADMIN_LID ?>" title="Список элементов" style="text-decoration: none">📋 Элементы</a>
        <!-- Административный раздел -->
        <?php if ($USER->IsAdmin()) { ?>
            &ensp; <button id="<?=$buttonId?>" class="adm-btn adm-btn-add" style="width: 110px">Действия</button>
        <?php } ?>
    </td>
    <?php if ($USER->IsAdmin()) { ?>
        <!-- Выпадающий список секций -->
        <td class="adm-detail-content-cell-r" style="float: left; margin-left: 10px;">🗂️ Секция:
            <?= $this->getFieldSelect(
                name: $sectionId . '[section]',
                values: $this->getSelectSections(),
                selected: $hlblock->sectionStructure?->id
            ) ?>
        </td>
        <!-- Сортировка поля -->
        <td class="adm-detail-content-cell-r" style="float: left;margin-left: 10px;">
            ↕️ Сортировка: <input type="text" name="<?= $sectionId ?>[sort]" size="3" value="<?= $hlblock->sort ?>">
        </td>
    <?php } ?>
</tr>

<script>
    BX.ready(function() {
        BX.bind(BX('<?= $buttonId ?>'), 'click', function(e) {
            let menuId = 'my_custom_menu_' + Math.random().toString(36).substring(2);

            BX.Main.MenuManager.show({
                id: menuId, // Обязательный параметр
                bindElement: this,
                items: [
                    {
                        text: "✏️️ Изменить",
                        href: "/bitrix/admin/highloadblock_entity_edit.php?ID=<?= $hlblock->id ?>&lang=<?= LANG_ADMIN_LID ?>"
                    },
                    {
                        text: "🛠️️️ Поля справочника",
                        href: "/bitrix/admin/userfield_admin.php?find_type=ENTITY_ID&set_filter=Y&find=HLBLOCK_<?= $hlblock->id ?>&lang=<?= LANG_ADMIN_LID ?>"
                    },
                    <?php if (CModule::IncludeModule('sprint.migration')) { ?>
                    {
                        text: "💾 Создать миграцию",
                        href: "/bitrix/admin/sprint_migrations.php?config=cfg"
                    },
                    <?php } ?>
                    {
                        delimiter: true
                    },
                    {
                        text: "❌️ Удалить справочник",
                        onclick: function() {
                            if (confirm("Вы действительно хотите удалить справочник и все его элементы?")) {
                                location.replace("/bitrix/admin/highloadblock_entity_edit.php?action=delete&ID=<?= $hlblock->id ?>&lang=<?= LANG_ADMIN_LID ?>")
                            }
                        }
                    }
                ]
            });

            BX.PreventDefault(e);
        });
    });
</script>