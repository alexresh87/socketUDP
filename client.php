<?php

use App\Pack\PackData;

require "config.php";

$packData = new PackData();

$packResponse = $packData
    ->setClientId(3)
    ->setCommandId(5)
    ->setData("ffefef fewfwef4543")
    ->packClient();

$fp = stream_socket_client("udp://$server_id:$server_port", $errno, $errstr);
if (!$fp) {
    echo "ОШИБКА: $errno - $errstr<br />\n";
}

for ($i = 0; $i < 5; $i++) {
    echo "Попытка ($i) отправить сообщение..." . PHP_EOL;
    fwrite($fp, $packResponse);
    $answerFromServer = fread($fp, 65549);
    if ($answerFromServer) {
        $recvData = $packData->unpackServer($answerFromServer);
        if ($recvData['flag_success_recv'] == 0) {
            echo "Сообщение успешно отправлено и получен ответ." . PHP_EOL;
        }
        fclose($fp);
        break;
    }
    sleep(2);
}

if ($i == 5) {
    echo "Произошла ошибка отправки/получения сообщения. Сервер недоступен." . PHP_EOL;
}
