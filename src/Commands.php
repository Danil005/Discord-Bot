<?php

namespace Fiks\MureDiscord;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Monolog\Logger;

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
    public array $args;
    public ?Logger $logger;

    public function __construct(?Message $message = null, ?Discord $discord = null, array $args = [], ?Logger $logger = null)
    {
        $this->message = $message;
        $this->discord = $discord;
        $this->args = $args;
        $this->logger = $logger;
    }

    public function run()
    {

    }
}