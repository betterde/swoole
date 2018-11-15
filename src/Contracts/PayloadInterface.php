<?php

namespace Betterde\Swoole\Contracts;

/**
 * 消息载荷接口
 *
 * Interface PayloadInterface
 * @package Betterde\Swoole\Contracts
 * Date: 2018/11/13
 * @author George
 */
interface PayloadInterface
{
    /**
     * 获取控制器
     *
     * Date: 2018/11/13
     * @author George
     * @return string
     */
    public function getController(): string;

    /**
     * 设置控制器
     *
     * Date: 2018/11/13
     * @author George
     * @param $controller
     */
    public function setController(string $controller): void;

    /**
     * 获取控消息指令
     *
     * Date: 2018/11/13
     * @author George
     * @return string
     */
    public function getAction(): string;

    /**
     * 设置消息指令
     *
     * Date: 2018/11/13
     * @author George
     * @param $action
     */
    public function setAction(string $action): void;

    /**
     * 获取消息内容
     *
     * Date: 2018/11/13
     * @author George
     * @return array
     */
    public function getContent(): array;

    /**
     * 设置消息内容
     *
     * Date: 2018/11/13
     * @author George
     * @param $content
     */
    public function setContent(array $content): void;
}
