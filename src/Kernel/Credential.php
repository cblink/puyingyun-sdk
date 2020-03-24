<?php

namespace Cblink\Puyingyun\Kernel;

use Cblink\Puyingyun\Application;
use Symfony\Contracts\Cache\ItemInterface;

class Credential
{
    use MakesHttpRequests;

    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function token($force = false): string
    {
        if ($force) {
            $this->app['cache']->delete($this->cacheKey());
        }

        return $this->app['cache']->get($this->cacheKey(), function (ItemInterface $item) {
            $result = $this->request('POST', '', [
                'headers' => [
                    'Action' => 'login',
                ],
                'json' => $this->credentials(),
            ]);

            $item->expiresAfter($result['expire_in'] - 500);

            $this->app['cache']->delete($this->tokenInfoCacheKey());
            $this->tokenInfo($result);

            return $result['access_token'];
        });
    }

    protected function tokenInfo($result = null)
    {
        return $this->app['cache']->get($this->tokenInfoCacheKey(), function (ItemInterface $item) use ($result) {
            if (is_null($result)) {
                return null;
            }

            $item->expiresAfter($result['expire_in'] - 500);
            return $result;
        });
    }

    protected function credentials(): array
    {
        return [
            'phone' => $this->app['config']->get('phone'),
            'password' => $this->app['config']->get('password'),
        ];
    }

    protected function cacheKey(): string
    {
        return 'printer.access_token.'.md5(json_encode($this->credentials()));
    }

    protected function tokenInfoCacheKey(): string
    {
        return 'printer.login.'.md5(json_encode($this->credentials()));
    }
}