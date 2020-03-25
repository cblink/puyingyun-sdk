<?php

namespace Cblink\Puyingyun;

class LaravelServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Application::class, function(){
            return new Application(config('services.weather.puyingyun'));
        });

        $this->app->alias(Application::class, 'puyingyun');
    }

    public function provides()
    {
        return [Application::class, 'puyingyun'];
    }
}