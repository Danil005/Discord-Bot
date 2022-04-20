<?php

namespace Fiks\MureDiscord\Commands\MessageCreate;

use Albion\API\Infrastructure\GameInfo\PlayerClient;
use Discord\Builders\MessageBuilder;
use Discord\WebSockets\Event;
use Fiks\MureDiscord\Commands;
use Fiks\MureDiscord\Utils\TextDB;

class RegisterCommand extends Commands
{
    public string $event = Event::MESSAGE_CREATE;

    public const ROLES_TAG = [
        '47 region' => '[47]',
        'Devils of Avalon' => '[DOA]',
        'S T R O N G' => '[STR]',
    ];

    public const ROLES = [
        'member' => '963915137109065770',
        '47 region' => '963914906929860628',
        'Devils of Avalon' => '963914906929860628',
        'S T R O N G' => '965640193388470282',
    ];

    public const ALLIANCE_NAME = 'B2P';
    public const ALLIANCE_NAME_2 = 'Born to porn';

    public function run()
    {
        # Ищем игрока
        $this->logger->info('Start find player: ' . $this->args['username']);
        # Сохраняем в переменные, т.к. в Callable не работает $this
        $message = $this->message;
        $args = $this->args;

        # Отправляем сообщение и ждем
        $message->reply('Ожидаем подтверждения от Albion Online...')->then(function ($msg) use ($args, $message) {
            # Создаем модель игрока
            $client = new PlayerClient();

            # Ищем игрока
            $client->searchPlayer($this->args['username'])->then(static function ($players) use ($args, $msg, $message) {

                # Если не нашли, то выдаем ошибку
                if (!empty($players)) {
                    $db = new TextDB([
                        'dir' => __DIR__ . '/../../../db'
                    ]);

                    if(count($players) == 1) {
                        # Если нашли, берем первого.
                        $player = $players[0];
                    } else {
                        foreach ($players as $pl) {
                            if(in_array($pl['GuildName'], array_flip(self::ROLES))) {
                                $player = $pl;
                            }
                        }
                    }

                    if(!in_array($player['GuildName'], array_flip(self::ROLES))) {
                        $messageText = "Вы не являетесь участником Альянса B2P.";
                        # Редактируем
                        $msg->edit(MessageBuilder::new()->setContent($messageText));
                        return;
                    }

                    $user = $db->select('users', ['nickname' => $player['Name']]);

                    if(empty($user)) {
                        $db->insert('users', [
                            'nickname' => $player['Name'],
                            'id' => $message->member->id,
                        ]);
                    } else {
                        $messageText = "Данный игрок уже зарегистрирован.";
                        $msg->edit(MessageBuilder::new()->setContent($messageText));
                        return;
                    }

                    # Составляем сообщение
                    $messageText = "Добро пожаловать, {$args['username']}! Вы из гильдии **{$player['GuildName']}**. Соответственные роли были выданы. ";
                    $messageText .= "Имя в Discord было изменено.";
                    # Редактируем
                    $msg->edit(MessageBuilder::new()->setContent($messageText));
                    # Изменяем имя в ДС
                    $message->member->setNickname(self::ROLES_TAG[$player['GuildName']] . ' ' . $player['Name']);
                    # Устанавливаем роли
                    $roles = [self::ROLES['member'], self::ROLES[$player['GuildName']]];
                    $message->member->addRole($roles[0]);
                    $message->member->addRole($roles[1]);

                } else {
                    $msg->edit(MessageBuilder::new()->setContent('Такого пользователя не существует на просторах Albion Online! :mushroom: '));
                }
            })->otherwise(static function () use ($msg) {
                $msg->edit(MessageBuilder::new()->setContent('Такого пользователя не существует на просторах Albion Online! :mushroom: '));
            })->wait();
        });
    }
}