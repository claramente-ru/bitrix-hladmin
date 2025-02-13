<?php
declare(strict_types=1);

namespace Claramente\Hladmin\Services;

use Bitrix\Highloadblock\HighloadBlockLangTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Claramente\Hladmin\Entity\ClaramenteHladminHlblocksTable;
use Claramente\Hladmin\Entity\ClaramenteHladminSectionsTable;
use Claramente\Hladmin\Structures\HlBlockStructure;
use Claramente\Hladmin\Structures\SectionStructure;

/**
 * Внутренний сервис для работы с highload
 */
class HighloadService
{
    /**
     * @var array
     */
    private static array $cacheGetHighloads = [];

    /**
     * @var ?array
     */
    private static ?array $cacheGetSections = null;

    /**
     * Получить список секций для справочников
     * @return SectionStructure[]
     */
    public function getSections(): array
    {
        if (is_null(self::$cacheGetSections)) {
            $result = [];
            // Шаг 1: Заберем список секций
            $sections = ClaramenteHladminSectionsTable::query()
                ->setSelect(['*'])
                ->setOrder([
                    'SORT' => 'ASC',
                    'NAME' => 'ASC',
                ])
                ->fetchAll();
            // Шаг 2: Соберем результат в массив структур
            foreach ($sections as $section) {
                $result[] = new SectionStructure(
                    id: intval($section['ID']),
                    code: $section['CODE'],
                    name: $section['NAME'],
                    sort: intval($section['SORT'])
                );
            }

            self::$cacheGetSections = $result;
        }

        return self::$cacheGetSections;
    }

    /**
     * Получить секцию
     * @param int $id
     * @return SectionStructure|null
     */
    public function getSectionById(int $id): ?SectionStructure
    {
        foreach ($this->getSections() as $section) {
            if ($section->id === $id) {
                return $section;
            }
        }

        return null;
    }

    /**
     * Получить справочник по ID
     * @param int $id
     * @return HlBlockStructure|null
     */
    public function getHighloadById(int $id): ?HlBlockStructure
    {
        $hlblocks = $this->getHighloads(['ID' => $id]);

        return $hlblocks ? current($hlblocks) : null;
    }

    /**
     * Получить список справочников
     * @param array $filter
     * @return HlBlockStructure[]
     */
    public function getHighloads(array $filter = []): array
    {
        $cacheKey = serialize($filter);
        if (is_array(self::$cacheGetHighloads[$cacheKey] ?? null)) {
            return self::$cacheGetHighloads[$cacheKey];
        }
        $result = [];
        // Шаг 1: Заберем список справочников
        $hls = HighloadBlockTable::query()
            ->setSelect(['*'])
            ->setFilter($filter)
            ->setOrder([
                'ID' => 'asc'
            ])
            ->fetchAll();
        // Шаг 2: Заберем имена справочников
        $hlNames = HighloadBlockLangTable::query()
            ->setSelect(['*'])
            ->setOrder([
                'ID' => 'asc'
            ])
            ->setFilter([
                'LID' => 'ru'
            ])
            ->fetchAll();
        // Шаг 3: Соберем массив имен вида ID => NAME
        $hlNames = array_combine(
            array_column($hlNames, 'ID'),
            array_column($hlNames, 'NAME')
        );
        // Шаг 4: Соберем список сортировок справочников
        $hlSections = ClaramenteHladminHlblocksTable::query()
            ->setSelect(['*'])
            ->setOrder([
                'SORT' => 'asc',
                'ID' => 'asc',
            ])
            ->setFilter([
                'HLBLOCK_ID' => array_column($hls, 'ID')
            ])
            ->fetchAll();
        // Шаг 5: Соберем массив hlSections вида HLBLOCK_ID => ClaramenteHladminHlblocksTable
        $hlSections = array_combine(
            array_column($hlSections, 'HLBLOCK_ID'),
            array_values($hlSections)
        );
        // Шаг 6: Соберем результат в массив структур
        foreach ($hls as $hl) {
            // Секция справочника
            $section = null;
            if (isset($hlSections[$hl['ID']]['SECTION_ID'])) {
                $section = $this->getSectionById(intval($hlSections[$hl['ID']]['SECTION_ID']));
            }
            // Сортировка справочника
            $sort = 100;
            if (is_numeric($hlSections[$hl['ID']]['SORT'] ?? null)) {
                $sort = intval($hlSections[$hl['ID']]['SORT']);
            }
            $name = !empty($hlNames[(string)$hl['ID']]) ? $hlNames[(string)$hl['ID']] : $hl['NAME'];
            $result[] = new HlBlockStructure(
                id: intval($hl['ID']),
                code: (string)$hl['NAME'],
                name: $name,
                tableName: (string)$hl['TABLE_NAME'],
                sectionStructure: $section,
                sort: $sort
            );
        }
        // Шаг 7: Сортируем справочники по sort и name
        usort($result, function ($a, $b) {
            /**
             * @var HlBlockStructure $a
             * @var HlBlockStructure $b
             */
            // Сначала сортируем по sort
            if ($a->sort !== $b->sort) {
                return $a->sort <=> $b->sort;
            }
            // Сортируем по name если sort равны
            return $a->name <= $b->name;
        });

        self::$cacheGetHighloads[$cacheKey] = $result;

        return self::$cacheGetHighloads[$cacheKey];
    }
}
