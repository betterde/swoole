<?php

namespace Betterde\Swoole\Server\Message;

use Betterde\Swoole\Contracts\PayloadInterface;

/**
 * Web Socket 报文载荷
 *
 * Date: 2018/11/13
 * @author George
 * @package Betterde\Swoole\Server\Message
 */
class Payload implements PayloadInterface
{
    /**
     * @var string $controller
     * Date: 2018/11/13
     * @author George
     */
    protected $controller;

    /**
     * @var string $action
     * Date: 2018/11/13
     * @author George
     */
    protected $action;

    /**
     * @var array $content
     * Date: 2018/11/13
     * @author George
     */
    protected $content;

    /**
     * 对应用户的 FD
     *
     * @var int $sender
     * Date: 2018/11/13
     * @author George
     */
    public $sender;

    /**
     * @var int $opcode
     * Date: 2018/11/13
     * @author George
     */
    public $opcode;

    /**
     * 重试次数
     *
     * @var int $tries
     * Date: 2018/11/17
     * @author George
     */
    public $tries;

    /**
     * Payload constructor.
     * @param $controller
     * @param $action
     * @param $content
     * @param $sender
     * @param $opcode
     * @param int $tries
     */
    public function __construct($controller, $action, $content, $sender, $opcode, int $tries)
    {
        $this->controller = $controller;
        $this->action = $action;
        $this->content = $content;
        $this->sender = $sender;
        $this->opcode = $opcode;
        $this->tries = $tries;
    }

    /**
     * Date: 2018/11/13
     * @author George
     * @return string
     */
    public function getController(): string
    {
        if (strpos($this->controller, '\\') !== 0) {
            return '\\' . $this->controller;
        }
        return $this->controller;
    }

    /**
     * Date: 2018/11/13
     * @author George
     * @param string $controller
     */
    public function setController(string $controller): void
    {
        $this->controller = $controller;
    }

    /**
     * Date: 2018/11/13
     * @author George
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Date: 2018/11/13
     * @author George
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * Date: 2018/11/13
     * @author George
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * Date: 2018/11/13
     * @author George
     * @param array $content
     */
    public function setContent(array $content): void
    {
        $this->content = $content;
    }

    /**
     * Date: 2018/11/13
     * @author George
     * @return int
     */
    public function getSender(): int
    {
        return $this->sender;
    }

    /**
     * Date: 2018/11/13
     * @author George
     * @param int $sender
     */
    public function setSender(int $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * Date: 2018/11/13
     * @author George
     * @return int
     */
    public function getOpcode(): int
    {
        return $this->opcode;
    }

    /**
     * Date: 2018/11/13
     * @author George
     * @param int $opcode
     */
    public function setOpcode(int $opcode): void
    {
        $this->opcode = $opcode;
    }

    /**
     * Date: 2018/11/17
     * @author George
     * @return int
     */
    public function getTries(): int
    {
        return $this->tries;
    }

    /**
     * Date: 2018/11/17
     * @author George
     * @param int $tries
     */
    public function setTries(int $tries): void
    {
        $this->tries = $tries;
    }
}
