<?php
declare(strict_types=1);

namespace Claramente\Hladmin\Structures;

/**
 * Структура HL блока
 */
class SectionStructure
{
    /**
     * @param int $id
     * @param string $code
     * @param string|null $name
     * @param int $sort
     */
    public function __construct(
        public int     $id,
        public string  $code,
        public ?string $name = null,
        public int     $sort = 0,
    )
    {
    }
}
