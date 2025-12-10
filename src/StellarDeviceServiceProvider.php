<?php
namespace StellarSecurity\DeviceApi;

use Illuminate\Support\ServiceProvider;

class StellarDeviceServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/stellar-device.php', 'stellar-device');

        $this->app->singleton(DeviceService::class, function () {
            return new DeviceService();
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/stellar-device.php' => config_path('stellar-device.php'),
        ]);
    }
}
