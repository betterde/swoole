<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 服务监听端口号
    |--------------------------------------------------------------------------
    */
    'port' => env('WEB_SOCKET_PORT', '9501'),

    /*
    |--------------------------------------------------------------------------
    | 服务绑定IP地址
    |--------------------------------------------------------------------------
    */
    'host' => env('WEB_SOCKET_HOST', '127.0.0.1'),

    /*
    |--------------------------------------------------------------------------
    | 服务通讯协议
    |--------------------------------------------------------------------------
    */
    'socket_type' => SWOOLE_SOCK_TCP,

    /*
    |--------------------------------------------------------------------------
    | 服务允许参数
    |--------------------------------------------------------------------------
    */
    'options' => [
        /*
        |--------------------------------------------------------------------------
        | 服务的worker进程数量
        |--------------------------------------------------------------------------
        |
        | 根据具体的任务处理时间，设定适当的值
        |
        */
        'worker_num' => env('WEB_SOCKET_WORKER_NUM', 8),

        /*
        |--------------------------------------------------------------------------
        | 服务线程的数量
        |--------------------------------------------------------------------------
        */
        'reactor_num' => env('WEB_SOCKET_REACTOR_NUM', 4),

        /*
        |--------------------------------------------------------------------------
        | 是否以守护进程方式启动服务
        |--------------------------------------------------------------------------
        */
        'daemonize' => env('WEB_SOCKET_DAEMONIZE', true),

        'task_worker_num' => env('SWOOLE_HTTP_TASK_WORKER_NUM', swoole_cpu_num()),
        'package_max_length' => 20 * 1024 * 1024,
        'buffer_output_size' => 10 * 1024 * 1024,
        'socket_buffer_size' => 128 * 1024 * 1024,
        'max_request' => 3000,
        'send_yield' => true,
        'ssl_cert_file' => null,
        'ssl_key_file' => null,

        /*
        |--------------------------------------------------------------------------
        | 允许跨域访问的地址
        |--------------------------------------------------------------------------
        */
        'origin' => env('WEB_SOCKET_ORIGIN', 'https://localhost'),

        /*
        |--------------------------------------------------------------------------
        | 最大心跳时间，配合 心跳超时检测间隔时间 一起使用
        |--------------------------------------------------------------------------
        */
        'heartbeat_idle_time' => env('WEB_SOCKET_MAX_HEARTBEAT_TIME', 30),

        /*
        |--------------------------------------------------------------------------
        | 心跳超时检测间隔时间
        |--------------------------------------------------------------------------
        |
        | 当开启用该设置以后，Swoole会在后台定时迭代所有链接
        | 验证最后通讯时间和当前时间差值，是否大于最大心跳时间
        |
        */
        'heartbeat_check_interval' => env('WEB_SOCKET_HEARTBEAT_CHECK_TIME', 5),

        /*
        |--------------------------------------------------------------------------
        | 服务PID文件存放位置
        |--------------------------------------------------------------------------
        */
        'pid_file' => storage_path('runtime/swoole.pid'),

        /*
        |--------------------------------------------------------------------------
        | 服务日志文件存放位置
        |--------------------------------------------------------------------------
        */
        'log_file' => storage_path('logs/swoole.log'),
    ],

    'resolved' => [
        'view', 'files', 'session', 'session.store', 'routes',
        'db', 'db.factory', 'cache', 'cache.store', 'config', 'cookie',
        'encrypter', 'hash', 'router', 'translator', 'url', 'log'
    ],

    /*
    |--------------------------------------------------------------------------
    | 定义逻辑控制器的命名空间
    |--------------------------------------------------------------------------
    */
    'im' => [
        'namespace' => 'App\Socket\Controllers',
        'default' => 'DefaultHandler@handler',
    ],

    /*
    |--------------------------------------------------------------------------
    | 用户在线列表缓存设置
    |--------------------------------------------------------------------------
    */
    'online' => [
        'database' => 'status',
        'user_to_session' => [
            'key' => '%s', // 用户UID
            'field' => '%d', // FD
            'value' => '%s-%s' // Client-Version
        ],
        'session_to_user' => 'fd_to_uid',
    ],

    /*
    |--------------------------------------------------------------------------
    | 是否允用户许多端登陆
    |--------------------------------------------------------------------------
    */
    'multiterminal' => env('WEB_SOCKET_MULTITERMINAL', false),

    /*
    |--------------------------------------------------------------------------
    | 定义Web Socket Kernel
    |--------------------------------------------------------------------------
    */
    'kernel' => \Betterde\Swoole\Server\Kernel::class,

    /*
    |--------------------------------------------------------------------------
    | 定义Web Socket 事件处理
    |--------------------------------------------------------------------------
    */
    'events' => \Betterde\Swoole\Server\ServiceEvent::class,

    /*
    |--------------------------------------------------------------------------
    | 定义Web Socket 消息分发器
    |--------------------------------------------------------------------------
    */
    'dispatcher' => \Betterde\Swoole\Server\Message\Dispatcher::class,

    /*
    |--------------------------------------------------------------------------
    | 定义用户状态维持服务
    |--------------------------------------------------------------------------
    */
    'status' => \Betterde\Swoole\Server\User\Status::class,

    /*
    |--------------------------------------------------------------------------
    | 是否允许客户端自定义控制器
    |--------------------------------------------------------------------------
    */
    'custom_controller' => false
];
