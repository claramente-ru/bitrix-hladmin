<?php
declare(strict_types=1);

namespace Claramente\Hladmin\Entity;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Claramente\Hladmin\Structures\SectionStructure;

/**
 * Таблица - claramente_hladmin_sections.
 * Здесь хранятся все секции для highload блоков
 */
final class ClaramenteHladminSectionsTable extends DataManager
{
    /**
     * Название таблицы
     * @return string
     */
    public static function getTableName(): string
    {
        return 'claramente_hladmin_sections';
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
            new StringField('NAME', [
                'size' => 255,
                'nullable' => false
            ]),
            new StringField('CODE', [
                'size' => 32,
                'nullable' => false
            ]),
            new IntegerField('SORT', [
                'default_value' => 100,
                'nullable' => false
            ])
        ];
    }

    /**
     * Получить секцию по коду
     * @param string $code
     * @return SectionStructure|null
     */
    public static function getByCode(string $code): ?SectionStructure
    {
        $section = self::query()
            ->setFilter([
                'CODE' => $code
            ])
            ->setSelect(['*'])
            ->fetch();
        if (! $section) {
            return null;
        }

        return new SectionStructure(
            id: intval($section['ID']),
            code: $section['CODE'],
            name: $section['NAME'],
            sort: intval($section['SORT'])
        );
    }

    /**
     * Получить секцию по id
     * @param int $id
     * @return SectionStructure|null
     */
    public static function getTabById(int $id): ?SectionStructure
    {
        $section = self::query()
            ->setFilter([
                'ID' => $id
            ])
            ->setSelect(['*'])
            ->fetch();

        if (! $section) {
            return null;
        }

        return new SectionStructure(
            id: intval($section['ID']),
            code: $section['CODE'],
            name: $section['NAME'],
            sort: intval($section['SORT'])
        );
    }
}
