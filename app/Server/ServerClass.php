<?php

namespace App\Server;

use App\Pack\PackData;

class ServerClass
{
    private $socket;
    private $peer;
    const SERVER_IP = "127.0.0.1";
    const SERVER_PORT = "1337";

    private $recvClosure;
    private $packData;

    public function __construct()
    {
        $this->socket = stream_socket_server("udp://" . self::SERVER_IP . ":" . self::SERVER_PORT, $errno, $errstr, STREAM_SERVER_BIND);
        if (!$this->socket) {
            throw new \Exception($errstr, $errno);
        }
        $this->packData = new PackData();
    }

    public function on(\Closure $recv)
    {
        $this->recvClosure[] = $recv;
        return $this;
    }

    public function start()
    {
        do {
            $pkt = stream_socket_recvfrom($this->socket, 65549, 0, $this->peer);
            $unpackRequest = $this->packData->unpackClient($pkt);
            $crcTest = crc32((string)$unpackRequest['client_id'] .
                (string)$unpackRequest['command_id'] .
                (string)$unpackRequest['length_data'] .
                (string)$unpackRequest['data'] .
                (string)$unpackRequest['time']
            );

            $flag_success_recv = 0;
            //Если контрольные суммы не сходятся
            if ($crcTest != $unpackRequest['crc']) {
                $flag_success_recv = 1;
            }

            foreach ($this->recvClosure as $item) {
                $item($this, $flag_success_recv, $unpackRequest);
            }
        } while ($pkt !== false);
    }

    public function sendAnswer($flag_success_recv)
    {
        $response = $this->packData->packServer($flag_success_recv);
        stream_socket_sendto($this->socket, $response, 0, $this->peer);
    }
}
