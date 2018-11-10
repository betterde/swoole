<?php

namespace Betterde\Swoole\Providers;

use Illuminate\Support\ServiceProvider;
use Betterde\Swoole\Console\StopServiceCommand;
use Betterde\Swoole\Console\StartServiceCommand;
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

    }
}
