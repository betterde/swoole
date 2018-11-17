<?php

namespace App\Socket\Controllers;

use Swoole\WebSocket\Server;
use Betterde\Swoole\Server\Message\Payload;
use Betterde\Swoole\Server\Socket\WebSocketController;

/**
 * 消息逻辑控制器
 *
 * Date: 2018/11/17
 * @author George
 * @package App\Socket\Controllers
 */
class MessageController extends WebSocketController
{
    /**
     * 收到消息处理逻辑
     *
     * Date: 2018/11/17
     * @author George
     * @param Server $server
     * @param Payload $payload
     * @return mixed|void
     */
    function send(Server $server, Payload $payload)
    {
        // TODO Your Logic
    }
}
