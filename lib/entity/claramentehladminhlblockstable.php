<?php
declare(strict_types=1);

namespace Claramente\Hladmin\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Таблица - claramente_hladmin_hlblocks.
 * Здесь хранятся привязки hl => section
 */
final class ClaramenteHladminHlblocksTable extends DataManager
{
    /**
     * Название таблицы
     * @return string
     */
    public static function getTableName(): string
    {
        return 'claramente_hladmin_hlblocks';
    }

    /**
     * Поля таблицы
     * @return array
     */
    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'is_autocomplete' => true
            ]),
            new IntegerField('HLBLOCK_ID', [
                'nullable' => false,
            ]),
            new IntegerField('SORT', [
                'default_value' => 100,
                'nullable' => false
            ]),
            new IntegerField('SECTION_ID', [
                'nullable' => true,
            ])
        ];
    }

    /**
     * Получить элемент по ID справочника
     * @param int $highloadId
     * @return array|null
     */
    public static function getByHlblockId(int $hlblockId): ?array
    {
        return self::query()
            ->setSelect(['*'])
            ->setFilter([
                'HLBLOCK_ID' => $hlblockId
            ])
            ->fetch() ?: null;
    }
}
