<?php

namespace Betterde\Swoole\Contracts;

use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * Web Socket Kernel
 *
 * Interface WebSocketKernel
 * @package Betterde\Swoole\Contracts
 * Date: 2018/11/14
 * @author George
 */
interface WebSocketKernel
{
    /**
     * Bootstrap the application for Socket requests.
     *
     * @return void
     */
    public function bootstrap();

    /**
     * 处理收到的消息
     *
     * Date: 2018/11/14
     * @author George
     * @param Server $server
     * @param Frame $frame
     * @return mixed
     */
    public function handle(Server $server, Frame $frame);

    /**
     * Perform any final actions for the request lifecycle.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    public function terminate($request, $response);

    /**
     * Get the Laravel application instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    public function getApplication();
}
