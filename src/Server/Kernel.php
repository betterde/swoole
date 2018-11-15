<?php

namespace Betterde\Swoole\Server;

use Exception;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Betterde\Swoole\Contracts\Dispatcher;
use Betterde\Swoole\Contracts\EventInterface;
use Betterde\Swoole\Contracts\WebSocketKernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;

/**
 * Socket Kernel
 *
 * Date: 2018/11/14
 * @author George
 * @package Betterde\Swoole\Server
 */
class Kernel implements WebSocketKernel
{
    /**
     * 应用实例
     *
     * @var \Illuminate\Foundation\Application $app
     * Date: 2018/11/14
     * @author George
     */
    protected $app;

    /**
     * 服务响应事件
     *
     * @var EventInterface $events
     * Date: 2018/11/14
     * @author George
     */
    protected $events;

    /**
     * 消息分发器
     *
     * @var Dispatcher $dispatcher
     * Date: 2018/11/14
     * @author George
     */
    protected $dispatcher;

    /**
     * 引导时需要加载的组件
     *
     * @var array $bootstrappers
     * Date: 2018/11/14
     * @author George
     */
    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * 中间件
     *
     * @var array $middlewares
     * Date: 2018/11/14
     * @author George
     */
    protected $middlewares = [];

    /**
     * Kernel constructor.
     * @param Application $app
     * @param Dispatcher $dispatcher
     * @param EventInterface $events
     */
    public function __construct(Application $app, EventInterface $events, Dispatcher $dispatcher)
    {
        $this->app = $app;
        $this->events = $events;
        $this->dispatcher = $dispatcher;
    }

    /**
     * 启动服务
     *
     * Date: 2018/11/14
     * @author George
     */
    public function bootstrap()
    {
        $this->app->bootstrapWith($this->bootstrappers());
    }

    /**
     * 消息处理逻辑
     *
     * Date: 2018/11/14
     * @author George
     * @param Server $server
     * @param Frame $frame
     * @return mixed|void
     */
    public function handle(Server $server, Frame $frame)
    {
        try {
            $this->dispatcher->dispatch($server, $frame);
        } catch (Exception $exception) {
            $this->reportException($exception);
        }
    }

    /**
     * Date: 2018/11/14
     * @author George
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function terminate($request, $response)
    {
        foreach ($this->middlewares as $middleware) {
            if (! is_string($middleware)) {
                continue;
            }
            list($name) = $this->parseMiddleware($middleware);
            $instance = $this->app->make($name);
            if (method_exists($instance, 'terminate')) {
                $instance->terminate($request, $response);
            }
        }
    }

    /**
     * 获取应用实例
     *
     * Date: 2018/11/14
     * @author George
     * @return Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * Parse a middleware string to get the name and parameters.
     *
     * @param  string  $middleware
     * @return array
     */
    protected function parseMiddleware($middleware)
    {
        list($name, $parameters) = array_pad(explode(':', $middleware, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Get the bootstrap classes for the application.
     *
     * @return array
     */
    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }

    /**
     *
     *
     * Date: 2018/11/14
     * @author George
     * @param Exception $exception
     */
    protected function reportException(Exception $exception)
    {
        $this->app[ExceptionHandler::class]->report($exception);
    }

    /**
     * Render the exception to a response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderException($request, Exception $e)
    {
        return $this->app[ExceptionHandler::class]->render($request, $e);
    }
}
