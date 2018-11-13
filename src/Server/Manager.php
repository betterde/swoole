<?php

namespace Betterde\Swoole\Server;

use App\Socket\Parser;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Facade;
use Illuminate\Foundation\Application;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Betterde\Swoole\Contracts\ParserInterface;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * 服务管理器
 *
 * Date: 2018/11/10
 * @author George
 * @package Betterde\Swoole\Server
 */
class Manager
{
    use Adapter;
    
    /**
     * Laravel Application 实例
     *
     * @var Application $app
     * Date: 2018/11/10
     * @author George
     */
    protected $app;

    /**
     * 服务器事件
     *
     * @var ServiceEvent $events
     * Date: 2018/11/10
     * @author George
     */
    protected $events;

    /**
     * 消息解析器
     *
     * @var Parser $parser
     * Date: 2018/11/10
     * @author George
     */
    protected $parser;

    /**
     * Manager constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $events = new ServiceEvent();
        $this->events = $events->getEvents();
        $this->app = $app;
        $parser = new Parser();
        $this->initialize($parser);
    }

    /**
     * 初始化
     *
     * Date: 2018/11/10
     * @author George
     * @param ParserInterface $parser
     */
    protected function initialize(ParserInterface $parser)
    {
        $this->setParser($parser);
        $this->registerListeners();
    }

    /**
     * 启动服务
     *
     * Date: 2018/11/10
     * @author George
     */
    public function run()
    {
        $this->app['swoole.server']->start();
    }

    /**
     * 关闭服务
     *
     * Date: 2018/11/10
     * @author George
     */
    public function stop()
    {
        $this->app['swoole.server']->shutdown();
    }

    /**
     * 注册事件监听器
     *
     * Date: 2018/11/10
     * @author George
     */
    protected function registerListeners()
    {
        foreach ($this->events as $event) {
            $listener = 'on' . ucfirst($event);

            if (method_exists($this, $listener)) {
                $this->app['swoole.server']->on($event, [$this, $listener]);
            } else {
                $this->app['swoole.server']->on($event, function () use ($event) {
                    $event = sprintf('swoole.%s', $event);

                    $this->app['events']->fire($event, func_get_args());
                });
            }
        }
    }

    /**
     * 获取解析器
     *
     * Date: 2018/11/10
     * @author George
     * @return Parser
     */
    public function getParser(): Parser
    {
        return $this->parser;
    }

    /**
     * 设置解析器
     *
     * Date: 2018/11/10
     * @author George
     * @param ParserInterface $parser
     */
    public function setParser(ParserInterface $parser): void
    {
        $this->parser = $parser;
    }

    /**
     * "onStart" listener.
     */
    public function onStart()
    {
        $this->setProcessName('master process');
        $this->createPidFile();

        $this->app['events']->fire('swoole.start', func_get_args());
    }

    /**
     * The listener of "managerStart" event.
     *
     * @return void
     */
    public function onManagerStart()
    {
        $this->setProcessName('manager process');
        $this->app['events']->fire('swoole.managerStart', func_get_args());
    }

    /**
     * Date: 2018/11/11
     * @author George
     * @param $server
     */
    public function onWorkerStart($server)
    {
        $this->clearCache();
        $this->setProcessName('worker process');

        $this->app['events']->fire('swoole.workerStart', func_get_args());

        // don't init laravel app in task workers
        if ($server->taskworker) {
            return;
        }

        // clear events instance in case of repeated listeners in worker process
        Facade::clearResolvedInstance('events');

        $kernel = $this->app->make(Kernel::class);
        $reflection = new \ReflectionObject($kernel);
        $bootstrappersMethod = $reflection->getMethod('bootstrappers');
        $bootstrappersMethod->setAccessible(true);
        $bootstrappers = $bootstrappersMethod->invoke($kernel);
        array_splice($bootstrappers, -2, 0, ['Illuminate\Foundation\Bootstrap\SetRequestForConsole']);
        $this->app->bootstrapWith($bootstrappers);
        $this->resolveInstances();
    }

    public function onMessage(Server $server, Frame $frame)
    {
        var_dump($frame);
    }

    /**
     * Date: 2018/11/13
     * @author George
     * @param SwooleRequest $swooleRequest
     * @param SwooleResponse $swooleResponse
     */
    public function onRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse)
    {
        $this->app->make('swoole.server')->push(1, 'oK', 1, true);
        $request = $this->transformRequest($swooleRequest);
        $kernel = $this->app->make(Kernel::class);

        /**
         * @var \Illuminate\Http\Response $response
         */
        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (!$response->headers->has('Date')) {
            $response->setDate(\DateTime::createFromFormat('U', time()));
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

    /**
     * Set onShutdown listener.
     */
    public function onShutdown()
    {
        $this->removePidFile();
    }

    /**
     * Gets pid file path.
     *
     * @return string
     */
    protected function getPidFile()
    {
        return $this->app['config']->get('swoole.options.pid_file');
    }

    /**
     * Create pid file.
     */
    protected function createPidFile()
    {
        $pidFile = $this->getPidFile();
        $pid = $this->app['swoole.server']->master_pid;

        file_put_contents($pidFile, $pid);
    }

    /**
     * Remove pid file.
     */
    protected function removePidFile()
    {
        $pidFile = $this->getPidFile();

        if (file_exists($pidFile)) {
            unlink($pidFile);
        }
    }

    /**
     * Clear APC or OPCache.
     */
    protected function clearCache()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    /**
     * Set process name.
     *
     * @codeCoverageIgnore
     * @param $process
     */
    protected function setProcessName($process)
    {
        // MacOS doesn't support modifying process name.
        if ($this->isMacOS() || $this->isInTesting()) {
            return;
        }
        $serverName = 'swoole_http_server';
        $appName = $this->app['config']->get('app.name', 'Laravel');

        $name = sprintf('%s: %s for %s', $serverName, $process, $appName);

        swoole_set_process_name($name);
    }

    /**
     * Date: 2018/11/10
     * @author George
     * @return bool
     */
    protected function isMacOS()
    {
        return PHP_OS === 'Darwin';
    }

    /**
     * Indicates if it's in phpunit environment.
     *
     * @return bool
     */
    protected function isInTesting()
    {
        return defined('IN_PHPUNIT') && IN_PHPUNIT;
    }

    /**
     * Date: 2018/11/10
     * @author George
     * @param Throwable $throwable
     */
    public function logServerError(Throwable $throwable)
    {
        $this->app[ExceptionHandler::class]->report($throwable);
    }
}
