<?php
declare(strict_types=1);

namespace chilimatic\lib\Cache\Engine\Adapter;

use chilimatic\lib\Cache\Engine\CacheTrait;
use chilimatic\lib\Cache\Engine\ICache;


class Memcached implements ICache
{
    use CacheTrait;

    public const IDX_PERSISTENT_ID = 'persistent_id';
    public const IDX_CALLBACK = 'callback';
    public const IDX_SERVER_LIST = 'server_list';
    public const IDX_OPTIONS = 'options';
    public const IDX_SERVER_HOST = 'host';
    public const IDX_SERVER_PORT = 'port';
    public const IDX_SERVER_WEIGHT = 'weight';

    /**
     * @var int
     */
    private const DEFAULT_PORT = 11211;

    /**
     * @var \Memcached
     */
    private $engine;

    /**
     * construct wrapper
     *
     * @param \stdClass|array $param
     */
    public function __construct($param = null)
    {

        if ($param instanceof \stdClass) {
            $param = json_decode(json_encode($param), true);
        }

        $this->setupEngine($param);
    }

    /**
     * @param array $param
     */
    private function setupEngine(array $param): void
    {
        $this->engine = new \Memcached(
            $param[self::IDX_PERSISTENT_ID] ?? null,
            $param[self::IDX_CALLBACK] ?? null
        );

        if (isset($param[self::IDX_SERVER_LIST])) {
            $serverList = $param[self::IDX_SERVER_LIST];
            if (\count($serverList) === 1) {
                $serverList = array_pop($serverList);
                $this->setConnected(
                    $this->engine->addServer(
                        $serverList[self::IDX_SERVER_HOST],
                        $serverList[self::IDX_SERVER_PORT] ?? self::DEFAULT_PORT,
                        $serverList[self::IDX_SERVER_WEIGHT] ?? null)
                );
            } else {
                $this->setConnected($this->engine->addServers($serverList));
            }

            if (!empty($param[self::IDX_OPTIONS])) {
                $this->engine->setOptions($param[self::IDX_OPTIONS]);
            }
        }

        // Get the Cache Listing
        $this->entryMetaData = $this->engine->get(self::KEY_CACHE_LIST);

        if ($this->entryMetaData === false) {
            $this->engine->add(self::KEY_CACHE_LIST, []);
            $this->entryMetaData = [];
        }

        // check sum for the saving process
        $this->md5Sum = md5(json_encode($this->entryMetaData));
    }


    /**
     * Save the cacheListing to memcached
     *
     * @return boolean
     */
    public function saveCacheListing() : bool
    {

        if (md5(json_encode($this->entryMetaData)) === $this->md5Sum) {
            return false;
        }

        return $this->engine->set(self::KEY_CACHE_LIST, $this->entryMetaData);
    }

    /**
     * returns the current status
     */
    public function getStatus() : array
    {

        return [
            'status'        => $this->engine->getStats(),
            'version'       => $this->engine->getVersion(),
            'server_list'   => $this->engine->getServerList()
        ];
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

        if ($this->engine->set($key, $value, $expiration)) {
            $this->updateMetaData($key, $expiration);
            $this->saveCacheListing();

            return true;
        }

        return false;
    }

    /**
     * @param string $key
     * @param null $cache_cb
     * @param null $cas_token
     * @return bool|mixed
     */
    public function get(string $key, $cache_cb = null, &$cas_token = null)
    {
        if (isset($this->entryMetaData[$key])) {
            if ($cas_token) {
                return $this->engine->get($key, $cache_cb, $cas_token);
            }

            return $this->engine->get($key, $cache_cb);
        }

        return false;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return (bool) $this->engine->get($key);
    }

    /**
     * flush the whole cache
     *
     * @param $delay integer
     *               delay in seconds
     *
     * @return boolean
     */
    public function flush(int $delay = 0) : bool
    {

        if ($this->engine->flush($delay)) {
            $this->entryMetaData = [];

            return true;
        }

        return false;
    }

    /**
     * add method
     *
     * @param $key        string
     * @param $value      mixed
     * @param $expiration int
     *
     * @return boolean
     */
    public function add(string $key, $value, int $expiration = null) : bool
    {
        return $this->set($key, $value, $expiration);
    }

    /**
     * delete method
     *
     * @param $key  string
     * @param $time int
     *
     * @return boolean
     */
    public function delete(string $key,int $time = 0) : bool
    {

        if ($this->engine->delete($key, $time)) {
            unset($this->entryMetaData [$key]);
            $this->saveCacheListing();

            return true;
        }

        return false;
    }

    /**
     * delete method multiserver pools
     *
     * @param $server_key string
     * @param $key        string
     * @param $time       int
     *
     * @return boolean
     */
    public function deleteByKey(string $server_key, string $key, int $time = 0) : bool
    {

        if ($this->engine->deleteByKey($server_key, $key, ($time ?: 0))) {
            unset($this->entryMetaData[$key]);
            $this->saveCacheListing();

            return true;
        }

        return false;
    }

    /**
     * @return \Memcached
     */
    public function getEngine(): \Memcached
    {
        return $this->engine;
    }
}
