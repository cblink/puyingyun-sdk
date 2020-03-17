<?php

namespace Cblink\PuyingyunSdk\Printer;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['printer'] = function ($pimple) {
            return new Client($pimple);
        };
    }
}