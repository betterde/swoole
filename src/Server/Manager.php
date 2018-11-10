<?php

namespace Betterde\Swoole\Server;

use Illuminate\Contracts\Container\Container;

class Manager
{
    use Adapter;
    
    /**
     * @var Container $app
     * Date: 2018/11/10
     * @author George
     */
    protected $app;

    /**
     * Manager constructor.
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    protected function initialize()
    {
        
    }
}
