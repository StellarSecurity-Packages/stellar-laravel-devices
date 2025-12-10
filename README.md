# Stellar Laravel Devices

stellarsecurity/stellar-laravel-devices is a Laravel package providing a complete client wrapper for the Stellar Devices API.

## Features
- Add/register devices
- Delete devices
- List all devices under an identifier
- Static human-friendly device name generator
- Laravel auto-discovery (service provider + facade)

## Installation
```bash
composer require stellarsecurity/stellar-laravel-devices
```

## Configuration
```bash
php artisan vendor:publish --provider="StellarSecurity\DeviceApi\StellarDeviceServiceProvider" --tag=config
```

Add to your `.env`:
```env
STELLAR_DEVICE_API_BASE_URL="https://stellardevicesapiprod.azurewebsites.net/api/"
APPSETTING_API_USERNAME_STELLAR_DEVICE_API="your-api-username"
APPSETTING_API_PASSWORD_STELLAR_DEVICE_API="your-api-password"
STELLAR_DEVICE_API_TIMEOUT=10
```

## Usage Example
```php
use StellarDevice;

// Add device
$response = StellarDevice::add('customer-123', 'Office MacBook', true);

// List devices
$response = StellarDevice::devices('customer-123');

// Delete device
$response = StellarDevice::delete('customer-123', 'Office MacBook');
```

## API Methods
- `add(string $identifier, string $name, bool $returnDevices = false)`
- `delete(string $identifier, string $name)`
- `devices(string $identifier)`
- `names()`

## License
Proprietary – part of the Stellar Security ecosystem.
