<?php

namespace Swoole {
    define('SWOOLE_BASE', 4);
    define('SWOOLE_THREAD', 2);
    define('SWOOLE_PROCESS', 3);
    define('SWOOLE_IPC_UNSOCK', 1);
    define('SWOOLE_IPC_MSGQUEUE', 2);
    define('SWOOLE_IPC_PREEMPTIVE', 3);
    define('SWOOLE_SOCK_TCP', 1);
    define('SWOOLE_SOCK_TCP6', 3);
    define('SWOOLE_SOCK_UDP', 2);
    define('SWOOLE_SOCK_UDP6', 4);
    define('SWOOLE_SOCK_UNIX_DGRAM', 5);
    define('SWOOLE_SOCK_UNIX_STREAM', 6);
    define('SWOOLE_TCP', 1);
    define('SWOOLE_TCP6', 3);
    define('SWOOLE_UDP', 2);
    define('SWOOLE_UDP6', 4);
    define('SWOOLE_UNIX_DGRAM', 5);
    define('SWOOLE_UNIX_STREAM', 6);
    define('SWOOLE_SOCK_SYNC', 0);
    define('SWOOLE_SOCK_ASYNC', 1);
    define('SWOOLE_SYNC', 2048);
    define('SWOOLE_ASYNC', 1024);
    define('SWOOLE_KEEP', 4096);
    define('SWOOLE_SSL', 512);
    define('SWOOLE_SSLv3_METHOD', 1);
    define('SWOOLE_SSLv3_SERVER_METHOD', 2);
    define('SWOOLE_SSLv3_CLIENT_METHOD', 3);
    define('SWOOLE_SSLv23_METHOD', 0);
    define('SWOOLE_SSLv23_SERVER_METHOD', 4);
    define('SWOOLE_SSLv23_CLIENT_METHOD', 5);
    define('SWOOLE_TLSv1_METHOD', 6);
    define('SWOOLE_TLSv1_SERVER_METHOD', 7);
    define('SWOOLE_TLSv1_CLIENT_METHOD', 8);
    define('SWOOLE_TLSv1_1_METHOD', 9);
    define('SWOOLE_TLSv1_1_SERVER_METHOD', 10);
    define('SWOOLE_TLSv1_1_CLIENT_METHOD', 11);
    define('SWOOLE_TLSv1_2_METHOD', 12);
    define('SWOOLE_TLSv1_2_SERVER_METHOD', 13);
    define('SWOOLE_TLSv1_2_CLIENT_METHOD', 14);
    define('SWOOLE_DTLSv1_METHOD', 15);
    define('SWOOLE_DTLSv1_SERVER_METHOD', 16);
    define('SWOOLE_DTLSv1_CLIENT_METHOD', 17);
    define('SWOOLE_EVENT_READ', 512);
    define('SWOOLE_EVENT_WRITE', 1024);
    define('SWOOLE_VERSION', '2.1.0');
    define('SWOOLE_ERROR_MALLOC_FAIL', 501);
    define('SWOOLE_ERROR_SYSTEM_CALL_FAIL', 502);
    define('SWOOLE_ERROR_PHP_FATAL_ERROR', 503);
    define('SWOOLE_ERROR_NAME_TOO_LONG', 504);
    define('SWOOLE_ERROR_INVALID_PARAMS', 505);
    define('SWOOLE_ERROR_FILE_NOT_EXIST', 700);
    define('SWOOLE_ERROR_FILE_TOO_LARGE', 701);
    define('SWOOLE_ERROR_FILE_EMPTY', 702);
    define('SWOOLE_ERROR_DNSLOOKUP_DUPLICATE_REQUEST', 703);
    define('SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED', 704);
    define('SWOOLE_ERROR_SESSION_CLOSED_BY_SERVER', 1001);
    define('SWOOLE_ERROR_SESSION_CLOSED_BY_CLIENT', 1002);
    define('SWOOLE_ERROR_SESSION_CLOSING', 1003);
    define('SWOOLE_ERROR_SESSION_CLOSED', 1004);
    define('SWOOLE_ERROR_SESSION_NOT_EXIST', 1005);
    define('SWOOLE_ERROR_SESSION_INVALID_ID', 1006);
    define('SWOOLE_ERROR_SESSION_DISCARD_TIMEOUT_DATA', 1007);
    define('SWOOLE_ERROR_OUTPUT_BUFFER_OVERFLOW', 1008);
    define('SWOOLE_ERROR_SSL_NOT_READY', 1009);
    define('SWOOLE_ERROR_SSL_CANNOT_USE_SENFILE', 1010);
    define('SWOOLE_ERROR_SSL_EMPTY_PEER_CERTIFICATE', 1011);
    define('SWOOLE_ERROR_SSL_VEFIRY_FAILED', 1012);
    define('SWOOLE_ERROR_SSL_BAD_CLIENT', 1013);
    define('SWOOLE_ERROR_SSL_BAD_PROTOCOL', 1014);
    define('SWOOLE_ERROR_PACKAGE_LENGTH_TOO_LARGE', 1201);
    define('SWOOLE_ERROR_DATA_LENGTH_TOO_LARGE', 1202);
    define('SWOOLE_ERROR_TASK_PACKAGE_TOO_BIG', 2001);
    define('SWOOLE_ERROR_TASK_DISPATCH_FAIL', 2002);
    define('SWOOLE_ERROR_HTTP2_STREAM_ID_TOO_BIG', 3001);
    define('SWOOLE_ERROR_HTTP2_STREAM_NO_HEADER', 3002);
    define('SWOOLE_ERROR_SOCKS5_UNSUPPORT_VERSION', 7001);
    define('SWOOLE_ERROR_SOCKS5_UNSUPPORT_METHOD', 7002);
    define('SWOOLE_ERROR_SOCKS5_AUTH_FAILED', 7003);
    define('SWOOLE_ERROR_SOCKS5_SERVER_ERROR', 7004);
    define('SWOOLE_ERROR_HTTP_PROXY_HANDSHAKE_ERROR', 8001);
    define('SWOOLE_ERROR_HTTP_INVALID_PROTOCOL', 8002);
    define('SWOOLE_ERROR_WEBSOCKET_BAD_CLIENT', 8501);
    define('SWOOLE_ERROR_WEBSOCKET_BAD_OPCODE', 8502);
    define('SWOOLE_ERROR_WEBSOCKET_UNCONNECTED', 8503);
    define('SWOOLE_ERROR_WEBSOCKET_HANDSHAKE_FAILED', 8504);
    define('SWOOLE_ERROR_SERVER_MUST_CREATED_BEFORE_CLIENT', 9001);
    define('SWOOLE_ERROR_SERVER_TOO_MANY_SOCKET', 9002);
    define('SWOOLE_ERROR_SERVER_WORKER_TERMINATED', 9003);
    define('SWOOLE_ERROR_SERVER_INVALID_LISTEN_PORT', 9004);
    define('SWOOLE_ERROR_SERVER_TOO_MANY_LISTEN_PORT', 9005);
    define('SWOOLE_ERROR_SERVER_PIPE_BUFFER_FULL', 9006);
    define('SWOOLE_ERROR_SERVER_NO_IDLE_WORKER', 9007);
    define('SWOOLE_ERROR_SERVER_ONLY_START_ONE', 9008);
    define('SWOOLE_ERROR_SERVER_WORKER_EXIT_TIMEOUT', 9009);
    define('SWOOLE_REDIS_MODE_MULTI', 0);
    define('SWOOLE_REDIS_MODE_PIPELINE', 1);
    define('SWOOLE_REDIS_TYPE_NOT_FOUND', 0);
    define('SWOOLE_REDIS_TYPE_STRING', 1);
    define('SWOOLE_REDIS_TYPE_SET', 2);
    define('SWOOLE_REDIS_TYPE_LIST', 3);
    define('SWOOLE_REDIS_TYPE_ZSET', 4);
    define('SWOOLE_REDIS_TYPE_HASH', 5);
    define('SWOOLE_AIO_BASE', 0);
    define('SWOOLE_AIO_LINUX', 1);
    define('SWOOLE_FILELOCK', 2);
    define('SWOOLE_MUTEX', 3);
    define('SWOOLE_SEM', 4);
    define('SWOOLE_RWLOCK', 1);
    define('SWOOLE_SPINLOCK', 5);
    define('WEBSOCKET_OPCODE_TEXT', 1);
    define('WEBSOCKET_OPCODE_BINARY', 2);
    define('WEBSOCKET_OPCODE_PING', 9);
    define('WEBSOCKET_STATUS_CONNECTION', 1);
    define('WEBSOCKET_STATUS_HANDSHAKE', 2);
    define('WEBSOCKET_STATUS_FRAME', 3);
    define('WEBSOCKET_STATUS_ACTIVE', 3);
    define('SWOOLE_FAST_PACK', 1);
    define('UNSERIALIZE_OBJECT_TO_ARRAY', 1);
    define('UNSERIALIZE_OBJECT_TO_STDCLASS', 2);

    class swoole_server extends Server
    {

    }
}

namespace Swoole\Http {
    class Server
    {

    }
}

namespace Swoole\WebSocket {

    class Server extends \Swoole\Http\Server
    {

        /**
         * 创建服务参数
         *
         * Server constructor.
         * @param string $host
         * @param int $port
         * @param int $mode
         * @param int $sock_type
         */
        public function __construct(string $host, int $port, int $mode = SWOOLE_PROCESS, int $sock_type = SWOOLE_SOCK_TCP)
        {

        }

        public function set(array $setting)
        {

        }
    }

    class swoole_websocket_server extends Server
    {

    }

    class swoole_websocket_frame extends Frame
    {

    }
}

/**
 * Swoole 内置函数，获取版本信息
 */
if (!function_exists('swoole_version')) {
    function swoole_version(): string
    {
        return '';
    }
}

/**
 * Swoole 内置函数，获取CPU数量
 */
if (! function_exists('swoole_cpu_num')) {
    function swoole_cpu_num(): int
    {
        return 0;
    }
}

