<?php

namespace Betterde\Swoole\Server;

use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Application;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * Laravel 适配器
 *
 * Trait Adapter
 * @package Betterde\Swoole\Server
 * Date: 2018/11/10
 * @author George
 */
trait Adapter
{
    /**
     * @var Application $app
     * Date: 2018/11/10
     * @author George
     */
    protected $app;

    /**
     * 将 Illuminate Response 转换为 Swoole Response
     *
     * Date: 2018/11/13
     * @author George
     * @param Response $response
     * @param SwooleResponse $swooleResponse
     */
    public function transformResponse(Response $response, SwooleResponse $swooleResponse)
    {
        $swooleResponse->status($response->getStatusCode());

        foreach ($response->headers->getCookies() as $cookie) {
            /**
             * @var Cookie $cookie
             */
            $swooleResponse->cookie(
                $cookie->getName(), $cookie->getValue(),
                $cookie->getExpiresTime(), $cookie->getPath(),
                $cookie->getDomain(), $cookie->isSecure(),
                $cookie->isHttpOnly()
            );
        }

        if ($response instanceof StreamedResponse && property_exists($response, 'output')) {
            $swooleResponse->end($response->output);
        } elseif ($response instanceof BinaryFileResponse) {
            $swooleResponse->sendfile($response->getFile()->getPathname());
        } else {
            $swooleResponse->end($response->getContent());
        }
    }

    /**
     * 将Swoole Request 转换成 Illuminate Request
     *
     * Date: 2018/11/13
     * @author George
     * @param SwooleRequest $request
     * @return \Illuminate\Http\Request
     */
    public function transformRequest(SwooleRequest $request)
    {
        $get = isset($request->get) ? $request->get : [];
        $post = isset($request->post) ? $request->post : [];
        $cookie = isset($request->cookie) ? $request->cookie : [];
        $files = isset($request->files) ? $request->files : [];
        $header = isset($request->header) ? $request->header : [];
        $server = isset($request->server) ? $request->server : [];
        $content = $request->rawContent();

        foreach ($server as $key => $value) {
            $key = strtoupper($key);
            $server[$key] = $value;
        }

        foreach ($header as $key => $value) {
            $key = str_replace('-', '_', $key);
            $key = strtoupper($key);

            if (! in_array($key, ['REMOTE_ADDR', 'SERVER_PORT', 'HTTPS'])) {
                $key = 'HTTP_' . $key;
            }

            $server[$key] = $value;
        }

        Request::enableHttpMethodParameterOverride();

        if ('cli-server' === PHP_SAPI) {
            if (array_key_exists('HTTP_CONTENT_LENGTH', $server)) {
                $server['CONTENT_LENGTH'] = $server['HTTP_CONTENT_LENGTH'];
            }
            if (array_key_exists('HTTP_CONTENT_TYPE', $server)) {
                $server['CONTENT_TYPE'] = $server['HTTP_CONTENT_TYPE'];
            }
        }

        $request = new BaseRequest($get, $post, [], $cookie, $files, $server, $content);

        if (0 === strpos($request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), array('PUT', 'DELETE', 'PATCH'))
        ) {
            parse_str($request->getContent(), $data);
            $request->request = new ParameterBag($data);
        }

        return Request::createFromBase($request);
    }

    /**
     * 引导
     *
     * Date: 2018/11/10
     * @author George
     */
    public function bootstrap()
    {
        $kernel = $this->app->make(Kernel::class);
        $reflection = new \ReflectionObject($kernel);
        $bootstrappersMethod = $reflection->getMethod('bootstrappers');
        $bootstrappersMethod->setAccessible(true);
        $bootstrappers = $bootstrappersMethod->invoke($kernel);
        array_splice($bootstrappers, -2, 0, ['Illuminate\Foundation\Bootstrap\SetRequestForConsole']);
        $this->app->bootstrapWith($bootstrappers);
        $this->resolveInstances();
    }

    /**
     * 获取应用实例
     *
     * Date: 2018/11/10
     * @author George
     * @return Container|\Illuminate\Contracts\Foundation\Application|Application
     */
    public function getApplication()
    {
        if (! $this->app instanceof Container) {
            $this->app = $this->loadApplication();
            $this->bootstrap();
        }

        return $this->app;
    }

    /**
     * 解析要用到的实例
     *
     * Date: 2018/11/10
     * @author George
     */
    public function resolveInstances()
    {
        $resolves = config('swoole.resolved');

        foreach ($resolves as $abstract) {
            if ($this->getApplication()->offsetExists($abstract)) {
                $this->getApplication()->make($abstract);
            }
        }
    }

    /**
     * 获取应用实例
     *
     * Date: 2018/11/10
     * @author George
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function loadApplication()
    {
        return require base_path('bootstrap/app.php');
    }
}
