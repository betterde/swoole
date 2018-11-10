<?php

namespace Betterde\Swoole\Contracts;

/**
 * 服务事件接口
 *
 * Interface EventInterface
 * @package Betterde\Swoole\Contracts
 * Date: 2018/11/10
 * @author George
 */
interface EventInterface
{
    /**
     * 获取服务器事件列表
     *
     * Date: 2018/11/10
     * @author George
     * @return array
     */
    public function getEvents(): array;
}
