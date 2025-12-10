<?php
namespace StellarSecurity\DeviceApi;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

class DeviceService
{
    protected string $baseUrl;
    protected ?string $username;
    protected ?string $password;
    protected int $timeout;

    public function __construct(?string $baseUrl = null, ?string $username = null, ?string $password = null, ?int $timeout = null)
    {
        $this->baseUrl  = rtrim($baseUrl ?? config('stellar-device.base_url'), '/') . '/';
        $this->username = $username ?? config('stellar-device.username');
        $this->password = $password ?? config('stellar-device.password');
        $this->timeout  = $timeout ?? (int) config('stellar-device.timeout', 10);
    }

    protected function http(): PendingRequest
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->retry(3)
            ->timeout($this->timeout);
    }

    public function add(string $identifier, string $name, bool $returnDevices = false): ?Response
    {
        try {
            return $this->http()->post(
                $this->baseUrl . 'v1/devicecontroller/add',
                ['identifier' => $identifier, 'name' => $name, 'returnDevices' => $returnDevices]
            );
        } catch (Throwable $th) {
            return null;
        }
    }

    public function delete(string $identifier, string $name): Response
    {
        return $this->http()->delete(
            $this->baseUrl . 'v1/devicecontroller/delete',
            ['identifier' => $identifier, 'name' => $name]
        );
    }

    public function devices(string $identifier): Response
    {
        return $this->http()->get(
            $this->baseUrl . 'v1/devicecontroller/devices',
            ['identifier' => $identifier]
        );
    }

    public function names(): array
    {
        return ['Allison','Arthur','Ana'];
    }
}
