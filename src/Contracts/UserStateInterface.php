<?php

namespace Betterde\Swoole\Contracts;

use Swoole\WebSocket\Server;

/**
 * 用户状态接口
 *
 * Interface UserStateInterface
 * @package Betterde\Swoole\Contracts
 * Date: 2018/11/15
 * @author George
 */
interface UserStateInterface
{
    /**
     * 用户上线处理逻辑
     *
     * Date: 2018/11/15
     * @author George
     * @param int $fd
     * @param $uid
     * @param string $client
     * @param string $version
     * @return mixed
     */
    public function online(int $fd, $uid, string $client, string $version);

    /**
     * 用户离线处理逻辑
     *
     * Date: 2018/11/15
     * @author George
     * @param int $fd
     * @return mixed
     */
    public function offline(int $fd);

    /**
     * 获取用户状态逻辑
     *
     * Date: 2018/11/15
     * @author George
     * @param $uid
     * @return bool
     */
    public function status($uid): bool;

    /**
     * 获取连接信息
     *
     * Date: 2018/11/15
     * @author George
     * @var int $fd
     * @return mixed
     */
    public function connection(int $fd);

    /**
     * 心跳检测
     *
     * Date: 2018/11/15
     * @author George
     * @var int $fd
     * @return mixed
     */
    public function heartbeat(int $fd);

    /**
     * 发送 ping 包
     *
     * Date: 2018/11/15
     * @author George
     * @var int $fd
     * @return mixed
     */
    public function ping(int $fd);

    /**
     * 清空所有用户状态
     *
     * Date: 2018/11/15
     * @author George
     * @return mixed
     */
    public function clear();
}
