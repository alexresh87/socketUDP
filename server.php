<?php

use App\Server\ServerClass;

require "config.php";

$server = new ServerClass();

$server->on(function ($context, $flag_success_recv, $data) {
    if ($flag_success_recv == 0) {
        echo "Данные успешно получены: " . $data['data'] . PHP_EOL;
    } else {
        echo "Произошла ошибка в получении данных";
    }
    $context->sendAnswer($flag_success_recv);
});

$server->start();
