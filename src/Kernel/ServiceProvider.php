<?php

namespace Cblink\Puyingyun\Kernel;

use Pimple\Container;
use GuzzleHttp\Client as GuzzleHttp;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        isset($pimple['request']) || $pimple['request'] = function () {
            return Request::createFromGlobals();
        };

        $pimple['http_client'] = function () {
            return new GuzzleHttp([
                'base_uri' => 'http://puyingcloud.cn/v2/printer/open/index.html',
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 5.0,
                'http_errors' => false,
            ]);
        };

        $pimple['credential'] = function ($pimple) {
            return new Credential($pimple);
        };

        $pimple['cache'] = function () {
            return new FilesystemAdapter();
        };
    }
}