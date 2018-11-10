<?php

namespace Betterde\Swoole\Server\Message;

use Illuminate\Support\Facades\App;
use Betterde\Swoole\Contracts\ParserInterface;

/**
 * 消息报文解析
 *
 * Date: 2018/11/10
 * @author George
 * @package Betterde\Swoole\Server\Message
 */
abstract class Parser implements ParserInterface
{
    /**
     * 消息处理策略
     *
     * @var array $strategies
     * Date: 2018/11/10
     * @author George
     */
    protected $strategies = [];

    /**
     * 执行策略逻辑
     *
     * Date: 2018/11/10
     * @author George
     * @param $server
     * @param $frame
     * @return bool
     */
    public function execute($server, $frame)
    {
        $skip = false;

        foreach ($this->strategies as $strategy) {
            $result = App::call($strategy . '@handle', [
                'server' => $server,
                'frame' => $frame
            ]);

            if ($result === true) {
                $skip = true;
                break;
            }
        }

        return $skip;
    }

    public function encode(string $event, $data)
    {
        // TODO: Implement encode() method.
    }

    public function decode($frame)
    {
        // TODO: Implement decode() method.
    }
}
