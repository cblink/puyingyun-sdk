<?php

namespace Cblink\PuyingyunSdk\Kernel;

use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Cblink\PuyingyunSdk\Application;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Cblink\PuyingyunSdk\Kernel\Exceptions\Error;
use Cblink\PuyingyunSdk\Kernel\Exceptions\ClientTokenExpireException;
use Cblink\PuyingyunSdk\Kernel\Exceptions\MethodRetryTooManyException;

class BaseClient
{
    use MakesHttpRequests;

    const MAX_RETRIES = 3;

    /**
     * @var Application
     *
     * @author 牟勇 <my24251325@gmail.com>
     */
    protected $app;

    /**
     * @var HandlerStack
     *
     * @author 牟勇 <my24251325@gmail.com>
     */
    protected $puyingyunHandlerStack;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param $action
     * @param array $data
     * @param int $retry
     * @return array
     *
     * @author 牟勇 <my24251325@gmail.com>
     */
    public function doAction($action, $data = [], $retry = 1)
    {
        if ($retry === -1) {
            throw new MethodRetryTooManyException("In SDK: Action: {$action} 重试次数过多", Error::TOKEN_EXPIRE_STATUS);
        }

        try {
            return $this->requestAction($action, [
                RequestOptions::JSON => $data,
            ]);
        } catch (ClientTokenExpireException $e) {
            $this->app['credential']->token(true);
            return $this->doAction($action, $data, --$retry);
        }
    }

    /**
     * @param $action
     * @param array $options
     * @param string $method
     * @return array
     * @throws Exceptions\ClientError
     * @throws Exceptions\Exception
     * @throws \Throwable
     * @author 牟勇 <my24251325@gmail.com>
     */
    protected function requestAction($action, array $options = [], $method = 'POST')
    {
        if (! $handler = $this->puyingyunHandlerStack) {
            $handler = HandlerStack::create();

            $handler->push(function (callable $handler) {
                return function (RequestInterface $request, array $options) use ($handler) {
                    $request = $request->withHeader('Access-Token', $this->app['credential']->token());
                    return $handler($request, $options);
                };
            });

            $this->puyingyunHandlerStack = $handler;
        }

        return $this
            ->withRetryMiddleware()
            ->withJsonHeader()
            ->withActionHeader($action)
            ->request($method, '', $options + compact('handler'));
    }

    protected function withActionHeader($method)
    {
        $this->puyingyunHandlerStack->push(function (callable $handler) use ($method) {
            return function (RequestInterface $request, array $options) use ($handler, $method) {
                $request = $request->withHeader('Action', $method);
                return $handler($request, $options);
            };
        });

        return $this;
    }


    protected function withJsonHeader()
    {
        $this->puyingyunHandlerStack->push(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $request = $request->withHeader('content-type', 'application/json');
                return $handler($request, $options);
            };
        });

        return $this;
    }

    public function withRetryMiddleware()
    {
        $this->puyingyunHandlerStack->push(Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null,
            $value = null,
            RequestException $exception = null
        ) {
            // 超过最大重试次数，不再重试
            if ($retries > static::MAX_RETRIES) {
                return false;
            }

            // 请求失败，继续重试
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                // 如果请求有响应，但是状态码大于等于500，继续重试(这里根据自己的业务而定)
                if ($response->getStatusCode() >= 500) {
                    return true;
                }
            }

            return false;
        }, function ($numberOfRetries) {
            return 1000 * $numberOfRetries;
        }));

        return $this;
    }

    protected function concat(RequestInterface $request, array $query = []): RequestInterface
    {
        parse_str($request->getUri()->getQuery(), $parsed);
        $query = http_build_query(array_merge($query, $parsed));

        return $request->withUri($request->getUri()->withQuery($query));
    }
}