<?php
declare(strict_types=1);

namespace chilimatic\lib\Cache\Engine\Adapter;

use chilimatic\lib\Cache\Engine\CacheFactory;
use chilimatic\lib\Cache\Engine\CacheTrait;
use chilimatic\lib\Cache\Engine\ICache;
use chilimatic\lib\Cache\Exception\CacheException;


class APCU implements ICache
{
    use CacheTrait;

    /**
     *
     * @link http://php.net/manual/en/apc.constants.php
     *
     * @var int
     */
    public const APCU_LIST_ACTIVE = 1;

    /**
     *
     * @link http://php.net/manual/en/apc.constants.php
     *
     * @var int
     */
    public const APCU_LIST_DELETED = 2;

    /**
     *
     * @link http://php.net/manual/en/apc.constants.php
     *
     * @var int
     */
    public const APCU_BIN_VERIFY_MD5 = 1;

    /**
     *
     * @link http://php.net/manual/en/apc.constants.php
     *
     * @var int
     */
    public const APCU_BIN_VERIFY_CRC32 = 2;


    /**
     * @param \stdClass $param
     *
     * @throws CacheException
     */
    public function __construct($param = null)
    {
        /**
         * check if the chache does exists
         */
        if (!\function_exists('apcu_cache_info')) {
            throw new CacheException ('APC Cache not installed', CacheFactory::ERROR_CACHE_MISSING, E_USER_ERROR, __FILE__, __LINE__);
        }

        // if we can establish the connection
        $this->setConnected(true);

        // Get the Cache Listing

        if ($this->exists(self::KEY_CACHE_LIST) === false) {
            apcu_add(self::KEY_CACHE_LIST, []);
            $this->entryMetaData = [];
        } else {
            $this->entryMetaData = apcu_fetch(self::KEY_CACHE_LIST);
        }

        // check sum for the saving process
        $this->md5Sum = md5(json_encode($this->entryMetaData));
    }

    /**
     * Save the cacheListing as an apc listing
     *
     * Although there is the possibility to list the cache otherwise
     *
     * @return boolean
     */
    public function saveCacheListing() : bool
    {
        if (md5(json_encode($this->entryMetaData)) === $this->md5Sum) {
            return false;
        }

        return apcu_store(self::KEY_CACHE_LIST, $this->entryMetaData);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool {
        return $this->exists($key);
    }

    /**
     * checks if a key exists
     *
     * @param $key
     *
     * @internal param bool|\string[] $keys A string, or an array of strings, that contain keys.
     *           A string, or an array of strings, that contain keys.
     *
     * @return bool string[]
     */
    public function exists(string $key) : bool
    {
        return apcu_exists($key);
    }


    /**
     * returns the shared memory info
     *
     * @link http://www.php.net/manual/de/function.apc-sma-info.php
     *
     * @param bool $limited
     *
     * @return array bool
     */
    public function sma_info($limited = false): array
    {
        return apcu_sma_info($limited);
    }

    /**
     * Info wrapper
     *
     * @param bool $limited
     *
     * @return array bool
     */
    public function info($limited = false): array
    {
        return [
            'cacheInfo' => apcu_cache_info($limited),
            'sharedMemory' => apcu_sma_info(),
        ];
    }

    /**
     * standard apc method
     *
     * @param
     *            $key
     * @param mixed $value
     * @param int $expiration
     *
     * @return bool
     */
    public function store($key, $value = null, int $expiration = 0): bool
    {
        return $this->set($key, $value, $expiration);
    }

    /**
     * set method
     *
     * @param $key        string
     * @param $value      mixed
     * @param $expiration int
     *
     * @return boolean
     */
    public function set(string $key, $value = null, int $expiration = 0) : bool
    {
        if (apcu_store($key, $value, $expiration)) {
            $this->updateMetaData($key, $expiration);
            $this->saveCacheListing();

            return true;
        }

        return false;
    }

    /**
     * A wrapper since APC standard functions are called that
     * way and it should be possible to use this as a stand alone class
     *
     * @param
     *            $key
     *
     * @return mixed
     */
    public function fetch($key)
    {
        return $this->get($key);
    }


    /**
     * get method
     *
     * @param $key string
     *
     * @return mixed
     */
    public function get(string $key = null)
    {
        if (isset ($this->entryMetaData [$key])) {
            $val = apcu_fetch($key, $success);

            return $success ? $val : $success;
        }

        return false;
    }

    /**
     * flush the whole cache
     *
     * @param $delay integer
     *               [not functional just for the interface]
     *               delay in seconds
     *
     * @return boolean
     */
    public function flush($delay = 0): bool
    {
        return apcu_clear_cache();
    }

    /**
     * add method calls set function
     *
     * @param $key        string
     * @param $value      mixed
     * @param $expiration int
     *
     * @return boolean
     */
    public function add($key, $value, $expiration = null): bool
    {
        return $this->set($key, $value, $expiration);
    }

    /**
     * delete method
     *
     * @param $key  string
     * @param $time int
     *              [no functionality]
     *
     * @return boolean
     */
    public function delete(string $key, int $time = 0) : bool
    {
        if (apcu_delete($key)) {
            unset ($this->entryMetaData[$key]);
            $this->saveCacheListing();

            return true;
        }

        return false;
    }

    /**
     * @return array|bool|mixed
     */
    public function getStatus()
    {
        return (array) apcu_cache_info();
    }
}