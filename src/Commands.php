<?php

namespace Fiks\MureDiscord;

use Discord\Discord;
use Discord\Parts\Channel\Message;

class Commands
{
    /**
     * Переменная типа события Discord
     *
     * @var string
     */
    public string $event = 'MESSAGE_CREATE';

    public ?Message $message;
    public ?Discord $discord;

    public function __construct(?Message $message = null, ?Discord $discord = null)
    {
        $this->message = $message;
        $this->discord = $discord;
    }

    public function run()
    {

    }
}