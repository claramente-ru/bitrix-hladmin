<?php
declare(strict_types=1);

namespace Claramente\Hladmin\Structures;

/**
 * Структура HL блока
 */
class HlBlockStructure
{
    /**
     * @param int $id
     * @param string $code
     * @param string|null $name
     * @param string|null $tableName
     * @param SectionStructure|null $sectionStructure
     * @param int $sort
     */
    public function __construct(
        public int               $id,
        public string            $code,
        public ?string           $name = null,
        public ?string           $tableName = null,
        public ?SectionStructure $sectionStructure = null,
        public int               $sort = 0
    )
    {
    }
}