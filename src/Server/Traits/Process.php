<?php

namespace Betterde\Swoole\Server\Traits;

/**
 * 进程相关方法
 *
 * Trait Process
 * @package Betterde\Swoole\Server\Traits
 * Date: 2018/11/15
 * @author George
 */
trait Process
{
    /**
     * 设置进程名称
     *
     * Date: 2018/11/15
     * @author George
     * @param $process
     */
    protected function setName($process)
    {
        if ($this->isMacOS() || $this->isInTesting()) {
            return;
        }
        $serverName = 'swoole_http_server';
        $appName = config('app.name', 'Laravel');

        $name = sprintf('%s: %s for %s', $serverName, $process, $appName);

        swoole_set_process_name($name);
    }

    /**
     * 判断是否为 Mac OS
     *
     * Date: 2018/11/10
     * @author George
     * @return bool
     */
    protected function isMacOS()
    {
        return PHP_OS === 'Darwin';
    }

    /**
     * 判断是否为
     *
     * Date: 2018/11/15
     * @author George
     * @return bool
     */
    protected function isInTesting()
    {
        return defined('IN_PHPUNIT') && IN_PHPUNIT;
    }

    /**
     * 获取PID文件路径
     *
     * Date: 2018/11/15
     * @author George
     * @return mixed
     */
    protected function getPidFile()
    {
        return config('swoole.options.pid_file');
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
}
