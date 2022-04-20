<?php

namespace Fiks\MureDiscord;

use Discord\Discord;
use Discord\Exceptions\IntentException;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Logger as Monolog;
use React\EventLoop\Loop;
use ReflectionException;

class Bot
{
    /**
     * Переменная Discord API
     *
     * @var Discord
     */
    private Discord $discord;

    /**
     * Конфигурации чат-бота
     *
     * @var array
     */
    private array $config;

    /**
     * Логирование
     *
     * @var Monolog
     */
    private Logger $log;

    /**
     * Сообщение, которое пришло
     * от чат-бота
     *
     * @var Message
     */
    private Message $message;

    /**
     * Конструктор дискорд бота
     *
     * @throws IntentException
     */
    public function __construct(array $config)
    {
        # Создаем объект конфига
        $this->config = $config;
        # Создаем логирование
        $this->log = new Monolog('DiscordPHP');
        # Выводим данные в консоль
        $this->log->pushHandler(new StreamHandler('php://stdout', Monolog::DEBUG));

        # Создаем экземпляр Discord API
        $this->discord = new Discord([
            'token'   => $config['BOT_TOKEN'],      # Передаем Токен из конфига
            'loop'    => Loop::get(),               # Передаем Loop реактивность
            'intents' => Intents::getAllIntents(),  # Передаем права доступа
            'logger'  => $this->log                 # Передаем сам Логгер для Discord API
        ]);
    }

    /**
     * Получаем все команды, которые были добавлены в файле:
     * config/route.php
     *
     * Метод Route::getAllCommands был сделан Private, чтобы
     * нельзя было обратиться к нему. Однако здесь, как исключение,
     * нам пришлось сделать метод доступным, чтобы получить
     * все добавленные команды.
     *
     * @return array
     * @throws \Exception
     */
    private function getCommands(): array
    {
        # Получаем все добавленные команды из route.php
        include(__DIR__ . './../config/route.php');
        # Получаем объект описания метода
        $reflectionMethod = new \ReflectionMethod(Route::class, 'getAllCommands');
        # Изменяем ему доступность. Данная мера выполнена единственный раз, хоть и нарушает
        # правила хорошего тона программирования.
        $reflectionMethod->setAccessible(true);

        try {
            # Выполняем наш метод и получаем массив добавленных классов
            $result = $reflectionMethod->invoke(new Route);

            # Если это строка, то у нас ошибка
            if(is_string($result)) {
                # Выдаем ошибку в логгер
                $this->log->error($result);
                # Выкидываем исключение и прерываем работу
                throw new \Exception($result);
            } else {
                return $result;
            }
        } catch (ReflectionException $e) {
            $this->log->error($e->getMessage());
        }
        # На случай, если сработал catch
        return [];
    }

    /**
     * Сортируем команды по Event
     *
     * @param array $commands
     * @return array
     */
    private function sortByEvents(array $commands): array
    {
        # Объявляем массив
        $sorted = [];

        # Сортируем по Event
        foreach ($commands as $command) {
            $sorted[$command['event']][] = $command;
        }

        # Выдаем результат
        return $sorted;
    }

    /**
     * @param string $event
     * @param array $commands
     * @return ?array
     */
    private function findCommand(string $event, array $commands): ?array
    {
        # Получаем команды по событию
        if(isset($commands[$event])) {
            $commands = $commands[$event];
        } else {
            return [];
        }

        # Удаляем префикс
        $message = str_replace($this->config['PREFIX'], '', $this->message->content);

        # Ищем саму команду
        foreach ($commands as $command) {
            # Убираем аргументы, которые были переданы
            $commandWithoutArgs = trim(preg_replace('/\{[^)]+\}/m', '', $command['command']));
            preg_match_all('/(?<=\{)([\s\S]+?)(?=\})/m', $command['command'], $args);

            # Если есть совпадение, возвращаем команду
            if($commandWithoutArgs == trim(explode(' ', $message)[0])) {
                return [
                    /**
                     * Command:
                     * Array(
                     *   "command": string,
                     *   "class": new Class
                     *   "event": const
                     * )
                     */
                    'command' => $command,
                    # Аргументы, которые есть в команде
                    'args' => $args,
                    # Само сообщение
                    'message' => $message,
                    # Команда без аргументов для удаления
                    'commandWithoutArgs' => $commandWithoutArgs,
                ];
            }
        }

        return null;
    }

    /**
     * Получить аргументы и передать в класс
     *
     * @param array|null $data
     * @return array|int
     */
    private function createArgs(array $data = null): array|int
    {
        if(!$data)
            return [];

        # Получаем все слова, которые прислал нам пользователь
        $message = explode(' ', trim(str_replace($data['commandWithoutArgs'], '', $data['message'])));
        # Массив с аргументами, где ключ - переменная из Route, а значение - переданные от пользователя
        $args = array_flip($data['args'][0]);

        $i = 0;
        foreach ($args as $key => $value) {
            if(!isset($message[$i])) {
                $this->message->reply('Простите, но команда неверная. Вы указали не все аргументы. Команда: **!' . $data['command']['command'] . '**');
                return -1;
            }

            $args[$key] = $message[$i];
            $i++;
        }

        return $args;
    }


    /**
     * Запуск Чат-бота.
     *
     * Бот работает в режиме Loop и постоянно обновляет данные
     * @throws \Exception # Вызывается, если в route.php неправильно указан путь до класса с командой
     */
    public function run()
    {
        # Получаем все команды
        $commands = $this->sortByEvents($this->getCommands());

        $this->log->info('Starting bot...');
        $this->discord->on('ready', function (Discord $discord) use ($commands) {
            $this->log->info('Bot enabled and ready');

            /**
             * Сообщения, которые приходят
             */
            $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) use ($commands) {
                $this->message = $message;
                $command = $this->findCommand(Event::MESSAGE_CREATE, $commands);

                $args = (array)$this->createArgs($command);
                $class = isset($command) ? (string) $command['command']['class'] : '';

                if($class) {
                    $cmd = new $class($message, $discord, $args, $this->log);
                    $cmd->run();
                }
            });

            /**
             * Сообщения, которые удаляют
             */
            $discord->on(Event::MESSAGE_DELETE, function (Message $message, Discord $discord) use ($commands) {
                $this->message = $message;
                $this->findCommand(Event::MESSAGE_DELETE, $commands);
            });

            /**
             * Сообщения, на которые вешают реакцию
             */
            $discord->on(Event::MESSAGE_REACTION_ADD, function (Message $message, Discord $discord) use ($commands) {
                $this->message = $message;
                $this->findCommand(Event::MESSAGE_REACTION_ADD, $commands);
            });

            /**
             * Сообщения, с которых убирают реакции
             */
            $discord->on(Event::MESSAGE_REACTION_REMOVE, function (Message $message, Discord $discord) use ($commands) {
                $this->message = $message;
                $this->findCommand(Event::MESSAGE_REACTION_REMOVE, $commands);
            });

            /**
             * Сообщения, которые обновляют
             */
            $discord->on(Event::MESSAGE_UPDATE, function (Message $message, Discord $discord) use ($commands) {
                $this->message = $message;
                $this->findCommand(Event::MESSAGE_UPDATE, $commands);
            });

            /**
             * Сообщения, с которых удаляют все эмоции
             */
            $discord->on(Event::MESSAGE_REACTION_REMOVE_ALL, function (Message $message, Discord $discord) use ($commands) {
                $this->message = $message;
                $this->findCommand(Event::MESSAGE_REACTION_REMOVE_ALL, $commands);
            });
        });
    }
}