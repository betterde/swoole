<?php

namespace Betterde\Swoole\Server\Strategies;

class HeartbeatStrategy
{
    /**
     * Date: 2018/11/10
     * @author George
     * @param $server
     * @param $frame
     * @return bool
     */
    public function handle($server, $frame)
    {
        $packet = $frame->data;
        $packetLength = strlen($packet);
        $payload = '';

        if (Packet::getPayload($packet)) {
            return false;
        }

        if ($isPing = Packet::isSocketType($packet, 'ping')) {
            $payload .= Packet::PONG;
        }

        if ($isPing && $packetLength > 1) {
            $payload .= substr($packet, 1, $packetLength - 1);
        }

        if ($isPing) {
            $server->push($frame->fd, $payload);
        }

        return true;
    }
}
