<?php

# Подключаем пакеты
use Fiks\MureDiscord\Bot;

include './vendor/autoload.php';

# Получаем Config
$config = json_decode(file_get_contents('./config/config.json'), true);


try {
    $bot = new Bot($config);
    $bot->run();
} catch (\Discord\Exceptions\IntentException $e) {
    echo $e->getMessage();
}



