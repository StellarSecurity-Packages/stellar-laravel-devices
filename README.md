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

### Custom device-name pool
By default, the package ships with a built-in JSON name pool at `resources/names.json` (thousands of names) to reduce collisions.

You can override the pool in `config/stellar-device.php`:

```php
return [
  // ...
  'names' => ['Allison', 'Arthur', 'Ana'],
  'names_path' => storage_path('app/device-names.json'),
];
```

The JSON file can be either:
- An array: `[...]`
- Or an object: `{ "names": [...] }`

## Usage Example
```php
use StellarDevice;

// Add device
$response = StellarDevice::add('customer-123', 'Office MacBook', true);

// Or: generate a human-friendly random name
$name = StellarDevice::randomName();
$response = StellarDevice::add('customer-123', $name, true);

// Or: let the package pick a random name for you
$response = StellarDevice::addRandom('customer-123', true);

// List devices
$response = StellarDevice::devices('customer-123');

// Delete device
$response = StellarDevice::delete('customer-123', 'Office MacBook');
```

## API Methods
- `add(string $identifier, string $name, bool $returnDevices = false)`
- `addRandom(string $identifier, bool $returnDevices = false)`
- `delete(string $identifier, string $name)`
- `devices(string $identifier)`
- `names(int $count = 25, bool $withSuffix = false)`
- `randomName(bool $withSuffix = false)`

## License
Proprietary – part of the Stellar Security ecosystem.
