<?php

namespace Fiks\MureDiscord;


use Discord\Http\Exceptions\NotFoundException;

class Route
{
    protected static array $commands = [];

    protected static string $namespaces = 'Fiks\\MureDiscord\\Commands';

    /**
     * Добавляем Namespace
     *
     * @param string $namespace
     * @param callable $callback
     */
    public static function namespaces(string $namespace, callable $callback)
    {
        self::$namespaces = $namespace;
        $callback();
    }

    public static function addCommand(string $command, mixed $class)
    {
        self::$commands[] = [
            'command' => $command,
            'class' => self::$namespaces . '\\' . $class
        ];
    }

    private static function getAllCommands()
    {
        # Проходимся по всем классам
        foreach (self::$commands as $key => $command) {

            # Проверяем существует ли класс
            if(class_exists($command['class'])) {
                # Создаем экземпляр класса команды
                $class = new $command['class']();
            } else {
                # Выдаем текст, чтобы выдать логи и прекратить запуск бота.
                return "Command class {$command['class']} not found. Remove this command from ./config/route.php";
            }
            # Вносим данные о типе события
            self::$commands[$key]['event'] = $class->event;
        }

        return self::$commands;
    }
}