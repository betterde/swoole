<?php

namespace Betterde\Swoole\Console;

use Swoole\Process;
use Illuminate\Console\Command;

/**
 * Swoole 服务管理命令
 *
 * Date: 2018/11/10
 * @author George
 * @package Betterde\Swoole\Console
 */
class ReloadServiceCommand extends Command
{
    /**
     * 定义命令格式
     *
     * @var string $signature
     * Date: 2018/11/10
     * @author George
     */
    protected $signature = 'swoole:reload';

    /**
     * 定义指令描述
     *
     * @var string $description
     * Date: 2018/11/10
     * @author George
     */
    protected $description = 'Reload swoole service.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file = config('swoole.options.pid_file');
        if (file_exists($file)) {
            $pid = (int) file_get_contents($file);
            if ($pid && $this->running($pid)) {
                $this->info('Stopping swoole server...');
                $this->kill($pid, SIGUSR1, 3);
                $this->info('Succesed');
            }
        } else {
            $this->info('Swoole server is not running');
        }

        return true;
    }

    /**
     * 结束进程
     *
     * Date: 2018/11/15
     * @author George
     * @param $pid
     * @param $sig
     * @param int $wait
     * @return bool
     */
    private function kill($pid, $sig, $wait = 0)
    {
        Process::kill($pid, $sig);

        if ($wait) {
            $start = time();
            do {
                if (! $this->running($pid)) {
                    break;
                }

                usleep(100000);
            } while (time() < $start + $wait);
        }

        return $this->running($pid);
    }

    /**
     * 检测服务是否处于运行状态
     *
     * Date: 2018/11/15
     * @author George
     * @param int $pid
     * @return bool
     */
    private function running(int $pid)
    {
        try {
            return Process::kill($pid, 0);
        } catch (Throwable $exception) {
            return false;
        }
    }
}
