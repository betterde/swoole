<?php

namespace Betterde\Swoole\Contracts;

use Swoole\WebSocket\Frame;

/**
 * 消息解析器
 *
 * Interface ParserInterface
 * @package Betterde\Swoole\Contracts
 * Date: 2018/11/10
 * @author George
 */
interface ParserInterface
{
    /**
     * 对消息进行编码
     *
     * Date: 2018/11/10
     * @author George
     * @param string $event
     * @param $data
     * @return mixed
     */
    public function encode(string $event, $data);

    /**
     * 对消息进行解码
     *
     * Date: 2018/11/10
     * @author George
     * @param Frame $frame
     * @return mixed
     */
    public function decode(Frame $frame);
}
