<?php
namespace StellarSecurity\DeviceApi\Facades;

use Illuminate\Support\Facades\Facade;

class StellarDevice extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \StellarSecurity\DeviceApi\DeviceService::class;
    }
}
