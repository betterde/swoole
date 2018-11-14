<?php

namespace Betterde\Swoole\Server\Socket;

use ReflectionClass;
use ReflectionMethod;
use Swoole\WebSocket\Server;
use Betterde\Swoole\Server\Message\Payload;

/**
 * 抽象的Web Socket 逻辑控制器
 *
 * Date: 2018/11/14
 * @author George
 * @package Betterde\Swoole\Server\Socket
 */
abstract class WebSocketController
{
    protected $methods = [];

    /**
     * @var array
     * Date: 2018/11/14
     * @author George
     */
    protected $attributes = [];

    /**
     * WebSocketController constructor.
     * @throws \ReflectionException
     */
    public function __construct()
    {
        $methods = [];
        $reflector = new ReflectionClass(static::class);
        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[] = $method->getName();
        }

        $this->methods = array_diff($methods, [
            '__hook','__destruct',
            '__clone','__construct','__call',
            '__callStatic','__get','__set',
            '__isset','__unset','__sleep',
            '__wakeup','__toString','__invoke',
            '__set_state','__clone','__debugInfo'
        ]);

        foreach ($reflector->getProperties() as $property) {
            //不重置静态变量
            if (($property->isPublic() || $property->isProtected()) && !$property->isStatic()) {
                $name = $property->getName();
                $this->setAttribute($name, $this->{$name});
            }
        }
    }

    /**
     * 定义默认方法
     *
     * Date: 2018/11/14
     * @author George
     * @param Server $server
     * @param Payload $payload
     */
    public function default(Server $server, Payload $payload)
    {
        $server->push($payload->getSender(), sprintf('Your FD: %d', $payload->getSender()));
    }

    /**
     * Date: 2018/11/14
     * @author George
     * @param Server $server
     * @param Payload $payload
     * @return mixed
     */
    abstract function message(Server $server, Payload $payload);

    /**
     * 魔术方法
     *
     * Date: 2018/11/14
     * @author George
     * @param string $key
     * @param $value
     */
    public function __set(string $key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * 设置属性
     *
     * Date: 2018/11/14
     * @author George
     * @param string $key
     * @param $value
     */
    private function setAttribute(string $key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
}
