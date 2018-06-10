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
    /**
     * cache object
     *
     * @var object
     */
    public $cache;

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
     * Constructor sets credentials for the Caching
     *
     * @param string $name
     * @param array $credentials
     *
     * @throws CacheException|\Exception
     */
    protected function __construct(string $name, $credentials = [])
    {
        $this->cache       = CacheFactory::make($name, $credentials);
        $this->cacheName   = \get_class($this->cache);
        $this->connected   = $this->cache->isConnected();
        $this->credentials = $credentials;
    }

    /**
     * singelton constructor
     *
     * @param \stdClass $param
     *
     * @return Cache
     * @throws CacheException
     */
    public static function getInstance($param = null): Cache
    {
        if (!self::$instance instanceof self) {
            $type        = $param->type;
            $credentials = property_exists($param, 'credentials') ? $param->credentials : null;

            self::$instance = new Cache($type, $credentials);
        }

        return self::$instance;
    }


    /**
     * set wrapper for caching
     *
     * @param string $key
     * @param mixed $value
     * @param int $expiration
     *
     * @return \chilimatic\lib\Cache\Engine\Cache|null $instance
     */
    public static function set(string $key, $value = null, $expiration = null)
    {
        if (!self::$instance) {
            return null;
        }

        self::$instance->cache->set($key, $value, $expiration);

        return self::$instance;
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

        return self::$instance->cache->get($key);
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

        return self::$instance->cache;
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

        return self::$instance->cache->getStatus();
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
}
    