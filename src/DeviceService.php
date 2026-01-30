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

    /**
     * Cached name pool to avoid reading/parsing JSON on every call.
     */
    protected static ?array $cachedNames = null;
    protected static ?string $cachedNamesKey = null;

    /**
     * Path to the built-in JSON file shipped with the package.
     */
    protected const DEFAULT_NAMES_JSON_PATH = __DIR__ . '/../resources/names.json';

    /**
     * Human-friendly device names.
     *
     * Keeping this pool reasonably small keeps the package lightweight,
     * while still giving a lot of variation when sampled randomly.
     */
    private const NAME_POOL = [
        'Allison','Arthur','Ana','Amina','Amir','Alina','Alicia','Alex','Aleksander','Ava','Ayaan',
        'Benjamin','Bilal','Bianca','Boris','Bruno','Celine','Carlos','Carmen','Charlie','Chloe','Clara',
        'Daniel','Daria','David','Dawid','Dina','Dorian','Elena','Elias','Elif','Emil','Emma','Enzo','Eva',
        'Farah','Fatima','Felix','Freja','Gabriel','Giulia','Hanna','Hassan','Ida','Ibrahim','Ilyas','Ines',
        'Isabella','Ivan','Jamal','Jana','Jonas','Joseph','Julia','Kamal','Karim','Katrine','Khadija','Lana',
        'Laura','Lea','Lena','Leo','Liam','Lina','Luca','Lucia','Mads','Mahdi','Maja','Malik','Maria','Mariam',
        'Mark','Mateo','Matilda','Mia','Mikael','Mila','Milo','Mohamed','Mohammad','Nadia','Nanna','Naomi','Nora',
        'Noah','Omar','Olivia','Oscar','Oskar','Pablo','Rania','Rasmus','Rayan','Renata','Rita','Rivka','Safa',
        'Sara','Sarah','Sebastian','Signe','Sofia','Sofie','Soren','Stefan','Talia','Tariq','Theo','Thomas','Tobias',
        'Valentina','Victor','Viktor','William','Yara','Yasin','Youssef','Yusuf','Zain','Zara','Zoe',
    ];

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

    protected function normalizeNamePool(array $names): array
    {
        $out = [];
        foreach ($names as $n) {
            if (!is_string($n)) {
                continue;
            }

            $t = trim($n);
            if ($t === '') {
                continue;
            }

            $out[] = $t;
        }

        // Unique + stable indices
        $out = array_values(array_unique($out));

        return $out;
    }

    protected function loadNamesFromJsonFile(string $path): array
    {
        try {
            if (!is_file($path)) {
                return [];
            }

            $raw = @file_get_contents($path);
            if ($raw === false || trim($raw) === '') {
                return [];
            }

            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                return [];
            }

            // Accept either: ["Allison", ...] or { "names": [ ... ] }
            $names = $decoded['names'] ?? $decoded;
            if (!is_array($names)) {
                return [];
            }

            return $this->normalizeNamePool($names);
        } catch (Throwable $th) {
            return [];
        }
    }

    protected function getNamePool(): array
    {
        $inline = config('stellar-device.names');
        if (is_array($inline) && count($inline) > 0) {
            $key = 'inline';
            if (self::$cachedNames !== null && self::$cachedNamesKey === $key) {
                return self::$cachedNames;
            }

            $pool = $this->normalizeNamePool($inline);
            if (count($pool) === 0) {
                $pool = self::NAME_POOL;
            }

            self::$cachedNames = $pool;
            self::$cachedNamesKey = $key;
            return $pool;
        }

        $path = config('stellar-device.names_path');
        $path = is_string($path) ? trim($path) : '';
        if ($path === '') {
            $path = self::DEFAULT_NAMES_JSON_PATH;
        }

        $key = 'file:' . $path;
        if (self::$cachedNames !== null && self::$cachedNamesKey === $key) {
            return self::$cachedNames;
        }

        $pool = $this->loadNamesFromJsonFile($path);
        if (count($pool) === 0 && $path !== self::DEFAULT_NAMES_JSON_PATH) {
            // Fallback to the built-in JSON if a custom path was invalid.
            $pool = $this->loadNamesFromJsonFile(self::DEFAULT_NAMES_JSON_PATH);
        }

        if (count($pool) === 0) {
            $pool = self::NAME_POOL;
        }

        self::$cachedNames = $pool;
        self::$cachedNamesKey = $key;
        return $pool;
    }

    /**
     * Returns a randomized list of suggested device names.
     *
     * Useful for UIs where you want to show “name suggestions”.
     */
    public function names(int $count = 25, bool $withSuffix = false): array
    {
        $count = max(1, min(500, $count));

        $pool = $this->getNamePool();
        // Shuffle the pool for random sampling.
        // (Fisher–Yates is what shuffle() does, and it's fine here.)
        shuffle($pool);

        $picked = array_slice($pool, 0, min($count, count($pool)));

        // If the caller asked for more than we have in the pool,
        // fill the rest by reusing names with numeric suffixes.
        while (count($picked) < $count) {
            $picked[] = $this->randomName(true);
        }

        if ($withSuffix) {
            $picked = array_map(fn ($n) => $this->withNumericSuffix($n), $picked);
        }

        return array_values($picked);
    }

    /**
     * Returns a single random device name.
     */
    public function randomName(bool $withSuffix = false): string
    {
        $pool = $this->getNamePool();

        // random_int() is cryptographically secure and avoids modulo bias.
        $idx = random_int(0, count($pool) - 1);
        $name = $pool[$idx];

        return $withSuffix ? $this->withNumericSuffix($name) : $name;
    }

    /**
     * Convenience: add a device with a random name.
     */
    public function addRandom(string $identifier, bool $returnDevices = false): ?Response
    {
        return $this->add($identifier, $this->randomName(false), $returnDevices);
    }

    private function withNumericSuffix(string $name): string
    {
        // 4 digits gives plenty of uniqueness without looking silly.
        $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        return $name . ' ' . $suffix;
    }
}
