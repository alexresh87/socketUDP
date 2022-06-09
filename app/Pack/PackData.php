<?php

namespace App\Pack;

class PackData
{
    private $client_id;
    private $command_id;
    private $length_data;
    private $data;

    public function setClientId(int $clientId): PackData
    {
        $this->client_id = $clientId;
        return $this;
    }

    public function setCommandId(int $commandId): PackData
    {
        $this->command_id = $commandId;
        return $this;
    }

    public function setData(string $data): PackData
    {
        $this->data = $data;
        $this->length_data = mb_strlen($data, '8bit');
        return $this;
    }

    //Упаковываем данные
    public function packClient()
    {
        $current_time = time();
        $crc = crc32($this->client_id . $this->command_id . $this->length_data . $this->data . $current_time);
        $pack1 = pack(
            "CCna*",
            $this->client_id,
            $this->command_id,
            $this->length_data,
            $this->data
        );
        return $pack1 . pack("x") . pack("N", $current_time) . pack("N", $crc);
    }

    //распаковываем клиентские данные
    public function unpackClient($packData)
    {
        $data1 = unpack("Cclient_id/Ccommand_id/nlength_data", $packData);
        $offset = 4;
        $length = $data1['length_data'];
        $unpack_str = "a" . $length . "data/x/Ntime/Ncrc";
        $data2 = unpack($unpack_str, $packData, $offset);
        return array_merge($data1, $data2);
    }

    public function packServer($flag_success_recv)
    {
        $crc = crc32("". 1 .  $flag_success_recv);
        $pack_server = pack(
            "CCN",
            1, //client_id всегда 1
            $flag_success_recv,
            $crc
        );
        return $pack_server;
    }

    //распаковываем клиентские данные
    public function unpackServer($packData): array
    {
        return unpack("Cclient_id/Cflag_success_recv/Ncrc", $packData);
    }
}
