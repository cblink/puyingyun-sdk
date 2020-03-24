<?php

namespace Cblink\Puyingyun\Kernel;

use GuzzleHttp\Exception\ClientException;

trait MakesHttpRequests
{
    use ApiTransformResponseTrait;

    protected $transform = true;


    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     *
     * @throws Exceptions\ClientError
     * @throws Exceptions\Exception
     * @throws \Throwable
     *
     * @author 牟勇 <my24251325@gmail.com>
     */
    public function request(string $method, string $uri, array $options = [])
    {
        try {
            $response = $this->app['http_client']->request($method, $uri, $options);
        } catch (\Throwable $e) {
            // token 过期，可能在其他地方登录了
            $json = '{"status":40312001,"msg":"Access Invalid"}';

            $msg = trim(strstr($e->getMessage(), $json));

            if (empty($msg)) {
                throw $e;
            }

            $response = $msg;
        }

        return $this->transform ? $this->transformResponse($response) : $response;
    }

    public function dontTransform()
    {
        $this->transform = false;

        return $this;
    }
}