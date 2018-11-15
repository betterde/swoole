<?php

namespace Betterde\Swoole\Server;

use App\Socket\Parser;
use Illuminate\Foundation\Application;
use Betterde\Swoole\Contracts\EventInterface;
use Illuminate\Contracts\Debug\ExceptionHandler;

/**
 * 服务管理器
 *
 * Date: 2018/11/10
 * @author George
 * @package Betterde\Swoole\Server
 */
class Manager
{
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
     * @param EventInterface $events
     */
    public function __construct(Application $app, EventInterface $events)
    {
        $this->events = $events;
        $this->app = $app;
        $this->initialize();
    }

    /**
     * 初始化
     *
     * Date: 2018/11/10
     * @author George
     */
    protected function initialize()
    {
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
        foreach ($this->events->getEvents() as $event) {
            $listener = 'on' . ucfirst($event);

            // 如果事件在对应的方法存在与Events实例中则注册，否则抛出全局事件
            if (method_exists($this->events, $listener)) {
                $this->app['swoole.server']->on($event, [$this->events, $listener]);
            } else {
                $this->app['swoole.server']->on($event, function () use ($event) {
                    $event = sprintf('swoole.%s', $event);

                    $this->app['events']->fire($event, func_get_args());
                });
            }
        }
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
