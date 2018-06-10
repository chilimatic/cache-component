<?php
declare(strict_types=1);
namespace chilimatic\lib\Cache\Engine;

use chilimatic\lib\Cache\Exception\CacheException;
use chilimatic\lib\Interfaces\ISingelton;

/**
 * Class Cache
 * @package chilimatic\lib\Cache\Engine
 */
class Cache implements ISingelton
{

    public const IDX_ADAPTER_NAME = 'adapter';
    public const IDX_PARAMETERS = 'param';

    /**
     * cache object
     *
     * @var ICache
     */
    private $engine;

    /**
     * login credentials if needed
     *
     * @var array
     */
    public $credentials;

    /**
     * checks if the cache is connected to a pool otherwise -> error
     *
     * @var bool
     */
    public $connected = false;

    /**
     * the name of the cache [memcache/memcached/redis/filebased/apc.....]
     *
     * @var string
     */
    private $cacheName;

    /**
     * singelton instance
     *
     * @var Cache
     */
    public static $instance;


    /**
     * @param string $name
     * @param array $credentials
     *
     * @throws CacheException|\Exception
     */
    protected function __construct(string $name, array $credentials = [])
    {
        $this->engine      = CacheFactory::make($name, $credentials);
        $this->cacheName   = $name;
        $this->connected   = $this->engine->isConnected();
        $this->credentials = $credentials;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     *
     * @return \chilimatic\lib\Cache\Engine\Cache|null $instance
     */
    public static function set(string $key, $value = null, int $expiration = 0): ?Cache
    {
        if (!self::$instance) {
            return null;
        }

        self::$instance->engine->set($key, $value, $expiration);

        return self::$instance;
    }

    /**
     * @param string $key
     * @return Cache|null
     */
    public static function delete(string $key): ?Cache
    {
        if (!self::$instance) {
            return null;
        }

        self::$instance->engine->delete($key);

        return self::$instance;
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        if (!self::$instance) {
            return false;
        }

        return self::$instance->engine->has($key);
    }


    /**
     * get wrapper for caching
     *
     * @param string $key
     *
     * @return null|mixed
     */
    public static function get(string $key)
    {
        if (!self::$instance) {
            return null;
        }

        return self::$instance->engine->get($key);
    }

    /**
     * singelton constructor
     *
     * @param \stdClass|array $param
     *
     * @return Cache
     * @throws CacheException
     */
    public static function getInstance($param = null): Cache
    {
        if (!self::$instance instanceof self) {
            $param = json_decode(json_encode($param), true);

            self::$instance = new Cache(
                $param[self::IDX_ADAPTER_NAME],
                $param[self::IDX_PARAMETERS] ?? []
            );
        }

        return self::$instance;
    }

    public static function destroy(): void
    {
        if (self::$instance instanceof self) {
            self::$instance = null;
        }
    }


    /**
     * gets the cache info
     *
     * @return array
     */
    public static function getStatus(): array
    {
        if (!self::$instance) {
            return null;
        }

        return self::$instance->engine->getStatus();
    }

    /**
     * gets the cache object
     *
     * @return object
     */
    public static function getCache()
    {
        if (!self::$instance) {
            return null;
        }

        return self::$instance->engine;
    }

    /**
     * @return boolean
     */
    public function isConnected() : bool
    {
        return $this->connected;
    }

    /**
     * @return array
     */
    public function getCredentials(): array
    {
        return $this->credentials;
    }

    /**
     * @param array $credentials
     */
    public function setCredentials(array $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * @return string
     */
    public function getCacheName(): string
    {
        return (string)$this->cacheName;
    }

    /**
     * @param string $cacheName
     * @return Cache
     */
    public function setCacheName(string $cacheName): self
    {
        $this->cacheName = $cacheName;
        return $this;
    }

    /**
     * @return ICache
     */
    public function getEngine(): ICache
    {
        return $this->engine;
    }
}
    