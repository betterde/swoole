<?php

namespace Betterde\Swoole\Coroutine\Connectors;

use Exception;
use Betterde\Swoole\Coroutine\PDO;
use Illuminate\Database\Connectors\MySqlConnector as LaravelMySqlConnector;

/**
 *
 *
 * Date: 2018/11/10
 * @author George
 * @package Betterde\Swoole\Coroutine\Connectors
 */
class MySqlConnector extends LaravelMySqlConnector
{
    /**
     * Create a new PDO connection instance.
     *
     * @param  string  $dsn
     * @param  string  $username
     * @param  string  $password
     * @param  array  $options
     * @return \PDO
     */
    protected function createPdoConnection($dsn, $username, $password, $options)
    {
        return new PDO($dsn, $username, $password, $options);
    }

    /**
     * Date: 2018/11/10
     * @author George
     * @param Exception $e
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $options
     * @return \PDO
     * @throws Exception
     */
    protected function tryAgainIfCausedByLostConnection(Exception $e, $dsn, $username, $password, $options)
    {
        // https://github.com/swoole/swoole-src/blob/a414e5e8fec580abb3dbd772d483e12976da708f/swoole_mysql_coro.c#L196
        if ($this->causedByLostConnection($e) || Str::contains($e->getMessage(), 'is closed')) {
            return $this->createPdoConnection($dsn, $username, $password, $options);
        }

        throw $e;
    }
}
