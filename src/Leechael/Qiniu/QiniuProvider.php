<?php

namespace Leechael\Qiniu;

use Illuminate\Support\ServiceProvider;

class QiniuProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('qiniu/qiniu');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('qiniu', function() {
            return new Client(\Config::get('qiniu::qiniu'));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('qiniu');
    }

}