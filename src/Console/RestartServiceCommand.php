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
class RestartServiceCommand extends Command
{
    /**
     * 定义命令格式
     *
     * @var string $signature
     * Date: 2018/11/10
     * @author George
     */
    protected $signature = 'swoole:restart';

    /**
     * 定义指令描述
     *
     * @var string $description
     * Date: 2018/11/10
     * @author George
     */
    protected $description = 'Restart swoole service.';

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
        $this->call('swoole:stop');
        $this->call('swoole:start');
        return true;
    }
}
