<?php

namespace Cblink\PuyingyunSdk;

use Pimple\Container;
use Cblink\PuyingyunSdk\Kernel\Config;

/**
 * Class Application
 * @package Cblink\PuyingyunSdk
 *
 * @property Printer\Client $printer
 * @property Kernel\Credential $credential
 *
 * @author 牟勇 <my24251325@gmail.com>
 */
class Application extends Container
{
    protected $providers = [
        Printer\ServiceProvider::class,
        Kernel\ServiceProvider::class,
    ];

    public function __construct(array $config)
    {
        parent::__construct();

        $this['config'] = function () use ($config) {
            return new Config($config);
        };

        foreach ($this->providers as $provider) {
            $this->register(new $provider($this));
        }
    }

    public function __get($name)
    {
        return $this[$name];
    }
}