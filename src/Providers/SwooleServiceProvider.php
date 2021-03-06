<?php

namespace Betterde\Swoole\Providers;

use Swoole\WebSocket\Server;
use Betterde\Swoole\Server\Manager;
use Illuminate\Support\ServiceProvider;
use Betterde\Swoole\Contracts\Dispatcher;
use Betterde\Swoole\Contracts\EventInterface;
use Betterde\Swoole\Contracts\WebSocketKernel;
use Betterde\Swoole\Console\StopServiceCommand;
use Betterde\Swoole\Console\StartServiceCommand;
use Betterde\Swoole\Contracts\UserStateInterface;
use Betterde\Swoole\Console\ReloadServiceCommand;
use Betterde\Swoole\Console\RestartServiceCommand;
use Betterde\Swoole\Console\DisplayEnvironmentsCommand;

/**
 * Swoole 服务提供者
 *
 * Date: 2018/11/10
 * @author George
 * @package Betterde\Swoole\Providers
 */
class SwooleServiceProvider extends ServiceProvider
{
    /**
     * 定义是否延迟加载
     *
     * @var bool $defer
     * Date: 2018/11/10
     * @author George
     */
    protected $defer = false;

    /**
     * 服务实例
     * 
     * @var \Swoole\WebSocket\Server $server
     * Date: 2018/11/10
     * @author George
     */
    protected static $server;

    /**
     * 加载配置文件和命令
     *
     * Date: 2018/11/15
     * @author George
     */
    public function boot()
    {
        // 发布配置文件到项目目录
        $this->publishes([
            __DIR__ . '/../../config/swoole.php' => config_path('swoole.php'),
            __DIR__ . '/../../templates/MessageController.tpl' => base_path('app/Socket/Controllers/MessageController.php')
        ]);

        // 注册服务管理命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                StartServiceCommand::class,
                StopServiceCommand::class,
                RestartServiceCommand::class,
                ReloadServiceCommand::class,
                DisplayEnvironmentsCommand::class
            ]);
        }
    }

    /**
     * 注册服务
     *
     * Date: 2018/11/15
     * @author George
     */
    public function register()
    {
        $this->registerServer();
        $this->registerKernel();
        $this->registerManager();
    }

    /**
     * 注册服务到容器
     *
     * Date: 2018/11/10
     * @author George
     */
    protected function registerServer()
    {
        // 注册到容器
        $this->app->singleton(\Betterde\Swoole\Facades\Server::class, function () {
            if (is_null(static::$server)) {
                $this->createSwooleServer();
                $this->configureSwooleServer();
            }
            return static::$server;
        });

        $this->app->alias(\Betterde\Swoole\Facades\Server::class, 'swoole.server');
    }

    /**
     * 注册消息服务内核
     *
     * Date: 2018/11/14
     * @author George
     */
    protected function registerKernel()
    {
        $this->app->singleton(WebSocketKernel::class, config('swoole.kernel'));
        $this->app->singleton(EventInterface::class, config('swoole.events'));
        $this->app->singleton(Dispatcher::class, config('swoole.dispatcher'));
        $this->app->singleton(UserStateInterface::class, config('swoole.status'));
    }

    /**
     * 注册服务管理器
     *
     * Date: 2018/11/10
     * @author George
     */
    protected function registerManager()
    {
        $this->app->singleton('swoole.manager', function ($app) {
            return new Manager($app, $this->app->make(EventInterface::class));
        });
    }

    /**
     * 加载服务配置
     *
     * Date: 2018/11/10
     * @author George
     */
    protected function configureSwooleServer()
    {
        $setting = config('swoole.options');
        static::$server->set($setting);
    }

    /**
     * 创建 Swoole 服务实例
     *
     * Date: 2018/11/10
     * @author George
     */
    protected function createSwooleServer()
    {
        static::$server = new Server(
            config('swoole.host'),
            config('swoole.port'),
            SWOOLE_PROCESS,
            config('swoole.socket_type')
        );
    }
}
