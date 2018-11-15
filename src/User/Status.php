<?php

namespace Betterde\Swoole\Server\User;

use Betterde\Swoole\Facades\Server;
use Illuminate\Support\Facades\Redis;
use Betterde\Swoole\Contracts\UserStateInterface;

/**
 * 用户状态维护
 *
 * Date: 2018/11/15
 * @author George
 * @package Betterde\Swoole\Server\User
 */
class Status implements UserStateInterface
{
    /**
     * @var Redis $redis
     * Date: 2018/11/15
     * @author George 
     */
    protected $redis;

    /**
     * Status constructor.
     */
    public function __construct()
    {
        $this->redis = Redis::connection(config('swoole.online.database'));
    }


    /**
     * 用户上线后绑定
     * 
     * Date: 2018/11/15
     * @author George
     * @param int $fd
     * @param $uid
     * @param string $client
     * @param string $version
     * @return bool
     */
    public function online(int $fd, $uid, string $client, string $version): bool
    {
        // 缓存FD和UID的对应关系
        $this->redis->hset(config('swoole.online.session_to_user'), $fd, $uid);

        // 如果不允许多端登陆，则关闭其他连接
        if (config('swoole.multiterminal') === false) {
            // 获取之前的连接信息
            $connections = $this->redis->hgetall(sprintf(config('swoole.online.user_to_session.key'), $uid));
            $sessions = array_keys($connections);

            if (count($sessions) > 0) {
                foreach ($sessions as $session) {
                    // 关闭连接
                    Server::close((int) $session);
                }

                // 删除之前的连接信息
                $this->redis->hdel(config('swoole.online.session_to_user'), array_keys($sessions));
            }
        }

        // 缓存UID和FD的对应关系以及客户端信息
        $this->redis->hset(
            sprintf(config('swoole.online.user_to_session.key'), $uid),
            sprintf(config('swoole.online.user_to_session.field'), $fd),
            sprintf(config('swoole.online.user_to_session.value'), $client, $version)
        );

        return true;
    }

    /**
     * 用户离线后解除绑定
     * 
     * Date: 2018/11/15
     * @author George
     * @param int $fd
     * @return mixed|void
     */
    public function offline(int $fd)
    {
        $uid = $this->redis->hget(config('swoole.online.session_to_user'), $fd);
        $this->redis->hdel(config('swoole.online.session_to_user'), [$fd]);
        
        if (config('swoole.multiterminal') === false) {
            $this->redis->del([$uid]);
        } else {
            $this->redis->hdel(sprintf(config('swoole.online.user_to_session.key'), $uid), [$fd]);
        }
    }

    /**
     * 检测用户在线状态
     *
     * Date: 2018/11/15
     * @author George
     * @param $uid
     * @return array|bool
     */
    public function status($uid): bool
    {
        $connections = $this->clients($uid);

        if (count($connections) > 0) {
            return $connections;
        }
        return false;
    }

    /**
     * 根据UID获取用户的客户端信息
     *
     * Date: 2018/11/15
     * @author George
     * @param $uid
     * @return array
     */
    public function clients($uid)
    {
        return $this->redis->hgetall(sprintf(config('swoole.online.user_to_session.key'), $uid));
    }

    public function connection(int $fd)
    {
        // TODO: Implement connection() method.
    }

    public function heartbeat(int $fd)
    {
        // TODO: Implement heartbeat() method.
    }

    public function ping(int $fd)
    {
        // TODO: Implement ping() method.
    }

    /**
     * 清除所有连接信息
     *
     * Date: 2018/11/15
     * @author George
     * @return mixed|void
     */
    public function clear()
    {
        $this->redis->flushdb();
    }
}
