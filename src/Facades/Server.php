<?php

namespace Betterde\Swoole\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Server Facade
 *
 * Date: 2018/11/10
 * @author George
 * @package Betterde\Swoole\Facades
 */
class Server extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * Date: 2018/11/10
     * @author George
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'swoole.server';
    }
}
