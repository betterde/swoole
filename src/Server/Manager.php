<?php

namespace Betterde\Swoole\Server;

use Illuminate\Foundation\Application;
use Betterde\Swoole\Server\Message\Parser;
use Illuminate\Contracts\Container\Container;
use Betterde\Swoole\Contracts\ParserInterface;

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
     * Laravel 实例
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
     * @param Container $app
     * @param ServiceEvent $events
     */
    public function __construct(Container $app, ServiceEvent $events)
    {
        $this->events = $events->getEvents();
        $this->app = $app;
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

        $this->container['events']->fire('swoole.start', func_get_args());
    }

    /**
     * The listener of "managerStart" event.
     *
     * @return void
     */
    public function onManagerStart()
    {
        $this->setProcessName('manager process');
        $this->container['events']->fire('swoole.managerStart', func_get_args());
    }

    /**
     * "onWorkerStart" listener.
     */
    public function onWorkerStart($server)
    {
        $this->clearCache();
        $this->setProcessName('worker process');

        $this->container['events']->fire('swoole.workerStart', func_get_args());

        // don't init laravel app in task workers
        if ($server->taskworker) {
            return;
        }

        // clear events instance in case of repeated listeners in worker process
        Facade::clearResolvedInstance('events');

        // prepare laravel app
        $this->getApplication();

        // bind after setting laravel app
        $this->bindToLaravelApp();

        // prepare websocket handler and routes
        if ($this->isWebsocket) {
            $this->prepareWebsocketHandler();
            $this->loadWebsocketRoutes();
        }
    }

    /**
     * "onRequest" listener.
     *
     * @param \Swoole\Http\Request $swooleRequest
     * @param \Swoole\Http\Response $swooleResponse
     */
    public function onRequest($swooleRequest, $swooleResponse)
    {
        $this->app['events']->fire('swoole.request');

        $this->resetOnRequest();
        $handleStatic = $this->container['config']->get('swoole_http.handle_static_files', true);
        $publicPath = $this->container['config']->get('swoole_http.server.public_path', base_path('public'));

        try {
            // handle static file request first
            if ($handleStatic && Request::handleStatic($swooleRequest, $swooleResponse, $publicPath)) {
                return;
            }
            // transform swoole request to illuminate request
            $illuminateRequest = Request::make($swooleRequest)->toIlluminate();

            // set current request to sandbox
            $this->app['swoole.sandbox']->setRequest($illuminateRequest);
            // enable sandbox
            $this->app['swoole.sandbox']->enable();

            // handle request via laravel/lumen's dispatcher
            $illuminateResponse = $this->app['swoole.sandbox']->run($illuminateRequest);
            $response = Response::make($illuminateResponse, $swooleResponse);
            $response->send();
        } catch (Throwable $e) {
            try {
                $exceptionResponse = $this->app[ExceptionHandler::class]->render($illuminateRequest, $e);
                $response = Response::make($exceptionResponse, $swooleResponse);
                $response->send();
            } catch (Throwable $e) {
                $this->logServerError($e);
            }
        } finally {
            // disable and recycle sandbox resource
            $this->app['swoole.sandbox']->disable();
        }
    }

    /**
     * Reset on every request.
     */
    protected function resetOnRequest()
    {
        // Reset websocket data
        if ($this->isWebsocket) {
            $this->app['swoole.websocket']->reset(true);
        }
    }

    /**
     * Set onTask listener.
     */
    public function onTask($server, $taskId, $srcWorkerId, $data)
    {
        $this->container['events']->fire('swoole.task', func_get_args());

        try {
            // push websocket message
            if (is_array($data)) {
                if ($this->isWebsocket
                    && array_key_exists('action', $data)
                    && $data['action'] === Websocket::PUSH_ACTION) {
                    $this->pushMessage($server, $data['data'] ?? []);
                }
                // push async task to queue
            } elseif (is_string($data)) {
                $decoded = json_decode($data, true);

                if (JSON_ERROR_NONE === json_last_error() && isset($decoded['job'])) {
                    (new SwooleTaskJob($this->container, $server, $data, $taskId, $srcWorkerId))->fire();
                }
            }
        } catch (Throwable $e) {
            $this->logServerError($e);
        }
    }

    /**
     * Set onFinish listener.
     */
    public function onFinish($server, $taskId, $data)
    {
        // task worker callback
        return;
    }

    /**
     * Set onShutdown listener.
     */
    public function onShutdown()
    {
        $this->removePidFile();
    }

    /**
     * Set bindings to Laravel app.
     */
    protected function bindToLaravelApp()
    {
        $this->bindSandbox();
        $this->bindSwooleTable();

        if ($this->isWebsocket) {
            $this->bindRoom();
            $this->bindWebsocket();
        }
    }

    /**
     * Bind sandbox to Laravel app container.
     */
    protected function bindSandbox()
    {
        $this->app->singleton(Sandbox::class, function ($app) {
            return new Sandbox($app);
        });
        $this->app->alias(Sandbox::class, 'swoole.sandbox');
    }

    /**
     * Gets pid file path.
     *
     * @return string
     */
    protected function getPidFile()
    {
        return $this->app['config']->get('swoole_http.server.options.pid_file');
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
