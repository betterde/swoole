<?php

namespace Betterde\Swoole\Server\Message;

use ReflectionClass;
use ReflectionMethod;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Betterde\Swoole\Exceptions\MessageException;
use Betterde\Swoole\Contracts\Dispatcher as DispatcherInterface;

/**
 * 消息分发
 *
 * Date: 2018/11/14
 * @author George
 * @package Betterde\Swoole\Server\Message
 */
class Dispatcher implements DispatcherInterface
{
    /**
     * @var string $namespace
     * Date: 2018/11/14
     * @author George
     */
    private $namespace;

    /**
     * Dispatcher constructor.
     */
    public function __construct()
    {
        $this->namespace = config('swoole.im.namespace', 'App\Socket\Controllers');
    }

    /**
     * 消息分发
     *
     * Date: 2018/11/14
     * @author George
     * @param Server $server
     * @param Frame $frame
     * @throws MessageException
     * @throws \ReflectionException
     */
    public function dispatch(Server $server, Frame $frame)
    {
        $payload = $this->validate($frame);
        $controller = $this->namespace . $payload->getController();
        $action = $payload->getAction();
        $instance = new $controller();
        $methods = [];
        $reflector = new ReflectionClass($controller);
        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[] = $method->getName();
        }

        $allow = array_diff($methods, [
            '__hook','__destruct',
            '__clone','__construct','__call',
            '__callStatic','__get','__set',
            '__isset','__unset','__sleep',
            '__wakeup','__toString','__invoke',
            '__set_state','__clone','__debugInfo'
        ]);

        if (array_search($action, $allow) !== false) {
            $instance->{$action}($server, $payload);
        } else {
            $server->push($frame->fd, json_encode([
                'message' => sprintf('action %s is undefined', $payload->getAction())
            ]));
        }
    }

    /**
     * Date: 2018/11/14
     * @author George
     * @param Frame $frame
     * @return Payload
     * @throws MessageException
     */
    private function validate(Frame &$frame): Payload
    {
        if ($frame->finish) {
            $data = json_decode($frame->data, JSON_UNESCAPED_UNICODE);
            list($controller, $action) = explode('@', config('swoole.im.default'));
            if (config('swoole.custom_controller')) {
                $controller = array_get($data, 'controller', $controller);
            }
            return new Payload(
                $controller,
                array_get($data, 'action', $action),
                array_get($data, 'body', []),
                $frame->fd,
                $frame->opcode
            );
        }

        throw new MessageException('Frame is not finish');
    }
}
