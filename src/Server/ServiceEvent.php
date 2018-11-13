<?php

namespace Betterde\Swoole\Server;

use Betterde\Swoole\Contracts\EventInterface;

/**
 * 服务事件
 *
 * Date: 2018/11/10
 * @author George
 * @package Betterde\Swoole\Server
 */
class ServiceEvent implements EventInterface
{
    /**
     * 服务事件
     *
     * @var array $events
     * Date: 2018/11/10
     * @author George
     */
    protected $events = [
        'start', 'shutDown', 'workerStart', 'workerStop', 'packet',
        'bufferFull', 'bufferEmpty', 'task', 'finish', 'pipeMessage',
        'workerError', 'managerStart', 'managerStop', 'request',
        'open', 'message', 'close'
    ];

    /**
     * Date: 2018/11/10
     * @author George
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Date: 2018/11/10
     * @author George
     * @param array $events
     */
    public function setEvents(array $events): void
    {
        $this->events = $events;
    }
}
