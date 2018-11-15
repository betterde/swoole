<?php

namespace Betterde\Swoole\Contracts;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * 服务事件接口
 *
 * Interface EventInterface
 * @package Betterde\Swoole\Contracts
 * Date: 2018/11/10
 * @author George
 */
interface EventInterface
{
    /**
     * 获取服务器事件列表
     *
     * Date: 2018/11/10
     * @author George
     * @return array
     */
    public function getEvents(): array;

    /**
     * 服务启动事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @return mixed
     */
    public function onStart(Server $server);

    /**
     * 服务关闭事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @return mixed
     */
    public function onShutdown(Server $server);

    /**
     * Worker 启动事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param int $worker_id
     * @return mixed
     */
    public function onWorkerStart(Server $server, int $worker_id);

    /**
     * Worker 停止事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param int $worker_id
     * @return mixed
     */
    public function onWorkerStop(Server $server, int $worker_id);

    /**
     * Worker 退出事件
     * 仅在开启异步重启后触发
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param int $worker_id
     * @return mixed
     */
    public function onWorkerExit(Server $server, int $worker_id);

    /**
     * Worker 进程发生异常后触发的事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param int $worker_id
     * @param int $worker_pid
     * @param int $exit_code
     * @param int $signal
     * @return mixed
     */
    public function onWorkerError(Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal);

    /**
     * 管理进程启动事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @return mixed
     */
    public function onManagerStart(Server $server);

    /**
     * 管理进程停止事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @return mixed
     */
    public function onManagerStop(Server $server);

    /**
     * 进程间消息事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param int $src_worker_id
     * @param mixed $message
     * @return mixed
     */
    public function onPipeMessage(Server $server, int $src_worker_id, $message);

    /**
     * 建立链接事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param int $fd
     * @param int $reactor_id
     * @return mixed
     */
    public function onConnect(Server $server, int $fd, int $reactor_id);

    /**
     * 与客户端握手事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Request $request
     * @param Response $response
     * @return bool
     */
    public function onHandShake(Request $request, Response $response): bool;

    /**
     * 收到客户端请求事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function onRequest(Request $request, Response $response);

    /**
     * 与客户端建立WebSocket 连接后事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param Request $request
     * @return mixed
     */
    public function onOpen(Server $server, Request $request);

    /**
     * 收到客户端WebSocket消息事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param Frame $frame
     * @return mixed
     */
    public function onMessage(Server $server, Frame $frame);

    /**
     * 关闭连接事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param int $fd
     * @param int $reactor_id
     * @return mixed
     */
    public function onClose(Server $server, int $fd, int $reactor_id);
}
