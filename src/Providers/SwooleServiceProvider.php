<?php

namespace Betterde\Swoole\Providers;

use Betterde\Swoole\Server\Manager;
use Swoole\WebSocket\Server;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\DatabaseManager;
use Betterde\Swoole\Console\StopServiceCommand;
use Betterde\Swoole\Console\StartServiceCommand;
use Betterde\Swoole\Console\ReloadServiceCommand;
use Betterde\Swoole\Console\RestartServiceCommand;
use Betterde\Swoole\Console\DisplayEnvironmentsCommand;
use Betterde\Swoole\Coroutine\Connectors\MySqlConnector;

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
    protected $defer = true;

    /**
     * 服务实例
     * 
     * @var \Swoole\WebSocket\Server $server
     * Date: 2018/11/10
     * @author George
     */
    protected static $server;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 发布配置文件到项目目录
        $this->publishes([
            __DIR__ . '/../../config/swoole.php' => config_path('swoole.php'),
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
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerServer();
        $this->registerManager();
//        $this->registerDatabaseDriver();
//        $this->registerSwooleQueueDriver();
    }

    /**
     * 注册异步队列驱动
     *
     * Date: 2018/11/10
     * @author George
     */
    protected function registerSwooleQueueDriver()
    {
        $this->app->afterResolving('queue', function (QueueManager $manager) {
            $manager->addConnector('swoole', function () {
                return new SwooleTaskConnector($this->app->make(Server::class));
            });
        });
    }

    /**
     * 注册协程数据库驱动
     *
     * Date: 2018/11/10
     * @author George
     */
    protected function registerDatabaseDriver()
    {
        $this->app->resolving('db', function (DatabaseManager $db) {
            $db->extend('mysql-coroutine', function ($config, $name) {
                $config['name'] = $name;
                $connection = function () use ($config) {
                    return (new MySqlConnector())->connect($config);
                };

                return new MySqlConnection(
                    $connection,
                    $config['database'],
                    $config['prefix'],
                    $config
                );
            });
        });
    }

    /**
     * 注册服务到容器
     *
     * Date: 2018/11/10
     * @author George
     */
    protected function registerServer()
    {
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
     * Date: 2018/11/10
     * @author George
     */
    protected function registerManager()
    {
        $this->app->singleton('swoole.manager', function ($app) {
            return new Manager($app);
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
