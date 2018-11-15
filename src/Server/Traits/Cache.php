<?php

namespace Betterde\Swoole\Server\Traits;

trait Cache
{
    /**
     * 清除缓存
     *
     * Date: 2018/11/15
     * @author George
     */
    protected function clearCache()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }
}
