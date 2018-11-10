<?php

namespace Betterde\Swoole\Console;

use Illuminate\Console\Command;

/**
 * Swoole 服务管理命令
 *
 * Date: 2018/11/10
 * @author George
 * @package Betterde\Swoole\Console
 */
class DisplayEnvironmentsCommand extends Command
{
    /**
     * 定义命令格式
     *
     * @var string $signature
     * Date: 2018/11/10
     * @author George
     */
    protected $signature = 'swoole:env';

    /**
     * 定义指令描述
     *
     * @var string $description
     * Date: 2018/11/10
     * @author George
     */
    protected $description = 'Display swoole service runtime environments.';

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
     * Date: 2018/11/10
     * @author George
     */
    public function handle()
    {
        $this->check();
        $this->environment();
    }

    /**
     * 显示运行环境信息
     *
     * Date: 2018/11/07
     * @author George
     */
    private function environment()
    {
        $headers = ['name', 'value'];
        $this->info("Swoole service environments");
        $environment = [
            ['Host', config('swoole.host')],
            ['Port', config('swoole.port')],
            ['Worker number', config('swoole.worker_num')],
            ['User', get_current_user()],
            ['Daemon', config('swoole.daemonize')],
            ['PHP Version', phpversion()],
            ['Swoole Version', phpversion('swoole')],
            ['PID File Path', config('swoole.pid_file')],
            ['Log File Path', config('swoole.log_file')]
        ];
        $this->table($headers, $environment);
    }

    /**
     * 检验运行环境是否满足需求
     *
     * Date: 2018/11/07
     * @author George
     */
    private function check()
    {
        // 判断运行环境是否是Windows系统
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->error("Swoole extension doesn't support Windows OS yet.");
            exit;
        }

        // 判断PHP版本是否符合要求
        if (version_compare(phpversion(), '7.1', '<')) {
            $this->error("Your PHP version must be higher than 7.1 to use coroutine.");
            exit;
        }

        // 判断是否安装了 Swoole 扩展
        if (!extension_loaded('swoole')) {
            $this->error("Can't detect Swoole extension installed.");
            exit;
        }

        // 判断 Swoole 扩展版本是否符合要求
        if (version_compare(swoole_version(), '4.0.0', '<')) {
            $this->error("Your Swoole version must be higher than 4.0 to use coroutine.");
            exit;
        }
    }
}
