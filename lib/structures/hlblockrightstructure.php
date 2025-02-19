<?php
declare(strict_types=1);

namespace Claramente\Hladmin\Structures;

use Claramente\Hladmin\Services\HighloadRightService;

/**
 * Структура доступа HL блока
 */
class HlBlockRightStructure
{
    /**
     * @param int $taskId Идентификатор сущности класса HighloadBlockRightsTable
     * @param string $accessCode Права доступа Группа: G[0-9], Пользователь: U[0-9], Создатель элемента: CR, Авторизованные пользователи: AU
     * @param string|null $groupTitle Название группы
     * @param int|null $hlId Идентификатор справочника
     */
    public function __construct(
        public int     $taskId,
        public string  $accessCode,
        public ?string $groupTitle = null,
        public ?int    $hlId = null,
    )
    {
    }
}
