<?php

namespace Betterde\Swoole\Server\Message;

use App\Http\Controllers\TestController;
use Betterde\Swoole\Exceptions\MessageException;
use Swoole\WebSocket\Frame;
use Illuminate\Support\Facades\App;
use Betterde\Swoole\Contracts\ParserInterface;
use Swoole\WebSocket\Server;

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

    /**
     * 对消息进行编码
     *
     * Date: 2018/11/13
     * @author George
     * @param string $event
     * @param $data
     * @return mixed|void
     */
    public function encode(string $event, $data)
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 对消息进行解码
     *
     * Date: 2018/11/13
     * @author George
     * @param Frame $frame
     * @return Payload|mixed
     * @throws MessageException
     */
    public function decode(Frame $frame)
    {
        if ($frame->finish) {
            $data = json_decode($frame->data, JSON_UNESCAPED_UNICODE);
            return new Payload(
                array_get($data, 'controller', TestController::class),
                array_get($data, 'action', 'default'),
                array_get($data, 'body', 'default'),
                $frame->fd,
                $frame->opcode
            );
        }

        throw new MessageException('Frame is not finish');
    }

    /**
     * 分发消息到指定逻辑
     *
     * Date: 2018/11/13
     * @author George
     * @param Server $server
     * @param Payload $payload
     */
    public function dispatch(Server $server, Payload $payload)
    {
        $controller = 'App\\Socket\\' . $payload->getController();
        $action = $payload->getAction();
        $instance = new $controller();
        $instance->{$action}($server, $payload);
    }
}
