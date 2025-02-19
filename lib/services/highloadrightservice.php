<?php
declare(strict_types=1);

namespace Claramente\Hladmin\Services;

use Bitrix\Main\GroupTable;
use Bitrix\Highloadblock\HighloadBlockRightsTable;
use Bitrix\Main\TaskTable;
use Claramente\Hladmin\Structures\HlBlockRightStructure;
use Claramente\Hladmin\Structures\HlBlockStructure;

/**
 * Внутренний сервис для работы с правами highload
 */
class HighloadRightService
{
    /**
     * @var HighloadService
     */
    private HighloadService $highloadService;

    /**
     * Кэширование значений для @see self::getHighloadRightTasks
     * @var array|null
     */
    private static ?array $cacheTasks = null;

    /**
     * Кэширование доступов
     * @var array|null
     */
    private static ?array $cachePermissions = null;

    public function __construct()
    {
        $this->highloadService = new HighloadService();
    }

    /**
     * Получить права Highload справочника
     * @param int|HlBlockStructure $highload
     * @return HlBlockRightStructure[]
     */
    public function getHighloadRights(int|HlBlockStructure $highload): array
    {
        if (is_int($highload)) {
            $highload = $this->highloadService->getHighloadById($highload);
        }
        if (! $highload) {
            return [];
        }
        // Шаг 1: Получим права из сущности b_hlblock_entity_rights
        $highloadTableRights = HighloadBlockRightsTable::query()
            ->setSelect(['*'])
            ->setFilter([
                '=HL_ID' => $highload->id
            ])
            ->fetchAll();

        // Шаг 2: Переведем права в ACCESS_CODE => TASK_ID
        $highloadRights = array_combine(
            array_column($highloadTableRights, 'ACCESS_CODE'),
            array_column($highloadTableRights, 'TASK_ID'),
        );

        // Шаг 3: Получим основные типы доступов и сопоставим их с правами справочника
        $rights = $this->getHighloadRegularRights();
        foreach ($rights as &$right) {
            // Сопоставим стандартные права с текущими правами справочника
            if (isset($highloadRights[$right->accessCode])) {
                $right->taskId = (int)$highloadRights[$right->accessCode];
            }
        }
        unset($right);

        // Шаг 3: Добавим дополнительные права которые отсутствуют в регулярных права
        foreach ($highloadRights as $accessCode => $taskId) {
            foreach ($rights as $right) {
                if ($accessCode === $right->accessCode) {
                    // Такое доступ уже имеется в регулярных
                    break 2;
                }
            }
            $rights[] = new HlBlockRightStructure(
                taskId: (int)$taskId,
                accessCode: $accessCode,
                groupTitle: $accessCode
            );
        }
        unset($right);

        return $rights;
    }

    /**
     * Получить права справочника по TASK_ID
     * @param int $taskId
     * @return array|null
     */
    public function getHighloadTaskById(int $taskId): ?array
    {
        return $this->getHighloadRightTasks()[(string)$taskId] ?? null;
    }

    /**
     * Получить права справочника по NAME
     * @param string $name
     * @return array|null
     */
    public function getHighloadTaskByName(string $name): ?array
    {
        foreach ($this->getHighloadRightTasks() as $rightTask) {
            if (strtolower($rightTask['NAME']) === strtolower($name)) {
                return $rightTask;
            }
        }

        return null;
    }

    /**
     * Получить права справочников TASK_ID из @see self::getHighloadRights
     * @return array
     */
    public function getHighloadRightTasks(): array
    {
        if (null === self::$cacheTasks) {
            // Шаг 1: Получим права справочника
            $tasks = TaskTable::query()
                ->setSelect(['*'])
                ->setFilter([
                    '=MODULE_ID' => 'highloadblock'
                ])
                ->fetchAll();

            // Шаг 2: Получим заголовок по NAME
            /**
             * @var array $MESS
             */
            include_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/highloadblock/lang/ru/admin/task_description.php';
            foreach ($tasks as &$task) {
                $task['TITLE'] = $MESS['TASK_NAME_' . strtoupper($task['NAME'])] ?? null;
            }
            unset($task);

            // Шаг 3: Составим справочник TASK_ID => array
            self::$cacheTasks = array_combine(
                array_column($tasks, 'ID'),
                array_values($tasks)
            );
        }

        return self::$cacheTasks;
    }

    /**
     * Проверка наличия прав к справочнику
     * @param int $hlblockId
     * @param string $access hl_element_read,hl_element_ write, hl_element_delete
     * @return bool
     */
    public function checkPermission(int $hlblockId, string $access = 'hl_element_read'): bool
    {
        global $USER;
        // Администраторам доступно все справочники
        if ($USER->IsAdmin()) {
            return true;
        }
        if (! isset(self::$cachePermissions[$USER->GetID()][$hlblockId])) {
            self::$cachePermissions[$USER->GetID()][$hlblockId] = HighloadBlockRightsTable::getOperationsName($hlblockId);
        }
        // Права на highload
        $rights = self::$cachePermissions[$USER->GetID()][$hlblockId];
        if (! $rights) {
            return false;
        }
        // Если имеется write или delete, то read по умолчанию доступен
        if ($access === 'hl_element_read') {
            return true;
        }

        return in_array($access, $rights);
    }

    /**
     * Получить регулярные доступы (без пользователей) для справочника
     * @return HlBlockRightStructure[]
     */
    protected function getHighloadRegularRights(): array
    {
        $defaultTaskId = 0; // Стандартный TASK_ID "Не устанавливать"
        $rights = [];
        // Шаг 1: Получим группы пользователей
        $groups = GroupTable::query()
            ->setSelect(['*'])
            ->setOrder(['ID' => 'ASC'])
            ->fetchAll();
        // Шаг 2: Установим права группы пользователей
        foreach ($groups as $group) {
            $rights[] = new HlBlockRightStructure(
                taskId: $defaultTaskId,
                accessCode: 'G' . $group['ID'],
                groupTitle: $group['NAME']
            );
        }
        // Шаг 3: Дополнительные типы ACCESS_CODE
        $rights[] = new HlBlockRightStructure(
            taskId: $defaultTaskId,
            accessCode: 'AU',
            groupTitle: 'Авторизованные пользователя'
        );
        $rights[] = new HlBlockRightStructure(
            taskId: $defaultTaskId,
            accessCode: 'CR',
            groupTitle: 'Пользователь, создавший элемент'
        );

        return $rights;
    }
}
