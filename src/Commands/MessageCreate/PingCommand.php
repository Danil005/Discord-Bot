<?php

namespace Fiks\MureDiscord\Commands\MessageCreate;

use Discord\WebSockets\Event;
use Fiks\MureDiscord\Commands;

class PingCommand extends Commands
{
    public string $event = Event::MESSAGE_DELETE;

    public function run()
    {

    }
}