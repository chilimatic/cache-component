<?php
declare(strict_types=1);

namespace chilimatic\lib\Cache\Engine\Adapter;

use chilimatic\lib\Cache\Engine\CacheTrait;
use chilimatic\lib\Cache\Engine\ICache;


class Memcached implements ICache
{
    /**
     * cache trait to reduce code duplication
     */
    use CacheTrait;

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
     * @param \stdClass $param
     */
    public function __construct($param = null)
    {
        $this->setupEngine($param);
    }

    private function setupEngine($param): void
    {
        if ($param instanceof \stdClass) {
            $param = json_decode(json_encode($param), true);
        }

        $this->engine = new \Memcached($param['persistent_id'] ?? null, $param['callback'] ?? null);

        if (isset($param['server_list'])) {
            $serverList = $param['server_list'];
            if (\count($serverList) === 1) {
                $serverList = array_pop($serverList);
                $this->setConnected(
                    $this->engine->addServer(
                        $serverList['host'],
                        $serverList['port'] ?? self::DEFAULT_PORT,
                        $serverList['weight'] ?? null)
                );
            } else {
                $this->setConnected($this->engine->addServers($serverList));
            }
        }

        // Get the Cache Listing
        $this->entryMetaData = $this->engine->get('cacheListing');

        if ($this->entryMetaData === false) {
            $this->engine->add('cacheListing', []);
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

        return $this->engine->set('cacheListing', $this->entryMetaData);
    }

    /**
     * a listing of all cached entries which have been
     * inserted through this wrapper
     *
     * @return boolean
     */
    public function listCache() : bool
    {

        $newList = [];

        foreach ($this->entryMetaData as $key => $val) {
            $newList[$key] = new \stdClass();

            foreach ($val as $sKey => $sval) {
                $newList[$key]->{$sKey} = $sval;
            }
        }

        $this->list = $newList;

        return true;
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
     * get method
     *
     * @param $key       string
     * @param $cache_cb  callable
     *                   [optional]
     * @param $cas_token float
     *                   [optional]
     *
     * @return mixed
     */
    public function get(string $key = null, $cache_cb = null, &$cas_token = null)
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
