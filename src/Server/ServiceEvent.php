<?php

namespace Betterde\Swoole\Server;

use DateTime;
use ReflectionObject;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Betterde\Swoole\Server\Traits\Cache;
use Swoole\Http\Request as SwooleRequest;
use Betterde\Swoole\Server\Traits\Process;
use Swoole\Http\Response as SwooleResponse;
use Betterde\Swoole\Server\Traits\HandShake;
use Betterde\Swoole\Contracts\EventInterface;
use Betterde\Swoole\Contracts\WebSocketKernel;
use Betterde\Swoole\Contracts\UserStateInterface;

/**
 * 服务事件
 *
 * Date: 2018/11/10
 * @author George
 * @package Betterde\Swoole\Server
 */
class ServiceEvent implements EventInterface
{
    use Process, Cache, Adapter, HandShake;

    /**
     * @var Application $app
     * Date: 2018/11/15
     * @author George
     */
    protected $app;

    protected $status;

    /**
     * 服务事件
     *
     * @var array $events
     * Date: 2018/11/10
     * @author George
     */
    protected $events = [
        'start', 'shutDown', 'workerStart', 'workerStop', 'packet',
        'bufferFull', 'bufferEmpty', 'task', 'finish', 'pipeMessage',
        'workerError', 'managerStart', 'managerStop', 'request',
        'handShake', 'open', 'message', 'close'
    ];

    /**
     * ServiceEvent constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->status = $this->app->make(UserStateInterface::class);
    }

    /**
     * Date: 2018/11/10
     * @author George
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Date: 2018/11/10
     * @author George
     * @param array $events
     */
    public function setEvents(array $events): void
    {
        $this->events = $events;
    }

    /**
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @return mixed|void
     */
    public function onStart(Server $server)
    {
        $this->setName('master process');
        $this->createPidFile();
        $this->app['events']->fire('swoole.start', func_get_args());
    }

    /**
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @return mixed|void
     */
    public function onShutdown(Server $server)
    {
        $this->removePidFile();
    }

    /**
     * Worker 进程启动事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param int $worker_id
     * @return mixed|void
     */
    public function onWorkerStart(Server $server, int $worker_id)
    {
        $this->clearCache();
        $this->setName('worker process');
        $this->app['events']->fire('swoole.workerStart', func_get_args());

        // clear events instance in case of repeated listeners in worker process
        Facade::clearResolvedInstance('events');

        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        $reflection = new ReflectionObject($kernel);
        $bootstrappersMethod = $reflection->getMethod('bootstrappers');
        $bootstrappersMethod->setAccessible(true);
        $bootstrappers = $bootstrappersMethod->invoke($kernel);
        array_splice($bootstrappers, -2, 0, ['Illuminate\Foundation\Bootstrap\SetRequestForConsole']);
        $this->app->bootstrapWith($bootstrappers);
        $this->resolveInstances();
    }

    public function onWorkerStop(Server $server, int $worker_id)
    {
        // TODO: Implement onWorkerStop() method.
    }

    public function onWorkerExit(Server $server, int $worker_id)
    {
        // TODO: Implement onWorkerExit() method.
    }

    public function onWorkerError(Server $server, int $worker_id, int $worker_pid, int $exit_code, int $signal)
    {
        // TODO: Implement onWorkerError() method.
    }

    public function onManagerStart(Server $server)
    {
        $this->setName('manager process');
        $this->app['events']->fire('swoole.managerStart', func_get_args());
    }

    public function onManagerStop(Server $server)
    {
        // TODO: Implement onManagerStop() method.
    }

    public function onPipeMessage(Server $server, int $src_worker_id, $message)
    {
        // TODO: Implement onPipeMessage() method.
    }

    public function onConnect(Server $server, int $fd, int $reactor_id)
    {
        // TODO: Implement onConnect() method.
    }

    /**
     * 客户端握手事件
     *
     * Date: 2018/11/15
     * @author George
     * @param SwooleRequest $swooleRequest
     * @param SwooleResponse $swooleResponse
     * @return bool|mixed
     */
    public function onHandShake(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): bool
    {
        $uid = array_get($swooleRequest->get, 'uid');
        $client = array_get($swooleRequest->get, 'client');
        $version = array_get($swooleRequest->get, 'version');

        if (empty($uid) || empty($client) || empty($version)) {
            return false;
        }

        if ($this->authentication()) {
            $this->status->online($swooleRequest->fd, $uid, $client, $version);
            return $this->handShake($swooleRequest, $swooleResponse);
        }
        return false;
    }

    /**
     * 用户发起请求
     *
     * Date: 2018/11/15
     * @author George
     * @param SwooleRequest $swooleRequest
     * @param SwooleResponse $swooleResponse
     */
    public function onRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse)
    {
        $request = $this->transformRequest($swooleRequest);
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);

        /**
         * @var \Illuminate\Http\Response $response
         */
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (!$response->headers->has('Date')) {
            $response->setDate(DateTime::createFromFormat('U', time()));
        }

        $headers = $response->headers->allPreserveCase();
        if (isset($headers['Set-Cookie'])) {
            unset($headers['Set-Cookie']);
        }

        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }

        $this->transformResponse($response, $swooleResponse);
    }

    public function onOpen(Server $server, SwooleRequest $swooleRequest)
    {
        // TODO: Implement onOpen() method.
    }

    /**
     * 收到客户端消息事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param Frame $frame
     * @return mixed|void
     */
    public function onMessage(Server $server, Frame $frame)
    {
        $kernel = $this->app->make(WebSocketKernel::class);
        $kernel->handle($server, $frame);
    }

    /**
     * 连接关闭事件
     *
     * Date: 2018/11/15
     * @author George
     * @param Server $server
     * @param int $fd
     * @param int $reactor_id
     * @return mixed|void
     */
    public function onClose(Server $server, int $fd, int $reactor_id)
    {
        $this->status->offline($fd);
    }

    /**
     * 用户认证逻辑
     *
     * Date: 2018/11/15
     * @author George
     * @return bool
     */
    public function authentication(): bool
    {
        return true;
    }
}
