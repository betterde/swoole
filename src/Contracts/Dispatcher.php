<?php

namespace Betterde\Swoole\Contracts;

use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * 消息分发器
 *
 * Interface Dispatcher
 * @package Betterde\Swoole\Contracts
 * Date: 2018/11/14
 * @author George
 */
interface Dispatcher
{
    public function dispatch(Server $server, Frame $frame);
}
