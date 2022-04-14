<?php

namespace Fiks\MureDiscord\Commands\MessageCreate;

use Discord\WebSockets\Event;
use Fiks\MureDiscord\Commands;

class RegisterCommand extends Commands
{
    public string $event = Event::MESSAGE_CREATE;

    public function run()
    {

    }
}