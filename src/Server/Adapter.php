<?php

namespace Betterde\Swoole\Server;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Container\Container;

/**
 * Laravel 适配器
 *
 * Trait Adapter
 * @package Betterde\Swoole\Server
 * Date: 2018/11/10
 * @author George
 */
trait Adapter
{
    /**
     * @var Application $app
     * Date: 2018/11/10
     * @author George
     */
    protected $app;

    /**
     * 引导
     *
     * Date: 2018/11/10
     * @author George
     */
    public function bootstrap()
    {
        $kernel = $this->app->make(Kernel::class);
        $reflection = new \ReflectionObject($kernel);
        $bootstrappersMethod = $reflection->getMethod('bootstrappers');
        $bootstrappersMethod->setAccessible(true);
        $bootstrappers = $bootstrappersMethod->invoke($kernel);
        array_splice($bootstrappers, -2, 0, ['Illuminate\Foundation\Bootstrap\SetRequestForConsole']);
        $this->app->bootstrapWith($bootstrappers);
        $this->resolveInstances();
    }

    /**
     * 获取应用实例
     *
     * Date: 2018/11/10
     * @author George
     * @return Container|\Illuminate\Contracts\Foundation\Application|Application
     */
    public function getApplication()
    {
        if (! $this->app instanceof Container) {
            $this->app = $this->loadApplication();
            $this->bootstrap();
        }

        return $this->app;
    }

    /**
     * 解析要用到的实例
     *
     * Date: 2018/11/10
     * @author George
     */
    public function resolveInstances()
    {
        $resolves = config('swoole.resolved');

        foreach ($resolves as $abstract) {
            if ($this->getApplication()->offsetExists($abstract)) {
                $this->getApplication()->make($abstract);
            }
        }
    }

    /**
     * 获取应用实例
     *
     * Date: 2018/11/10
     * @author George
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function loadApplication()
    {
        return require base_path('bootstrap/app.php');
    }
}
