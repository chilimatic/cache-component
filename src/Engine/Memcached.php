<?php

namespace chilimatic\lib\Cache\Engine;

/**
 * Class CacheMemcached
 *
 * @package chilimatic\cache
 */
class Memcached extends \Memcached implements ICache
{
    /**
     * cache trait to reduce code duplication
     */
    use CacheTrait;

    /**
     * @var int
     */
    const DEFAULT_PORT = 11211;

    /**
     * construct wrapper
     *
     * @param \stdClass $param
     *
     * @return \chilimatic\lib\Cache\Engine\Memcached
     */
    public function __construct($param = null)
    {

        parent::__construct(isset($param->persistent_id) ? $param->persistent_id : null, isset($param->callback) ? $param->callback : null);

        if (isset($param->server_list)) {
            $server = $param->server_list;
            if (count($server) === 1) {
                $server = array_pop($server);
                $this->setConnected(
                    parent::addServer(
                        $server->host,
                        $server->port ?? self::DEFAULT_PORT,
                        isset($server->weight) ? $server->weight : null)
                );
            } else {
                $this->setConnected(parent::addServers($server));
            }
        }

        // Get the Cache Listing
        $this->cacheListing = parent::get('cacheListing');

        if ($this->cacheListing === false) {
            parent::add('cacheListing', array());
            $this->cacheListing = array();
        }

        // check sum for the saving process
        $this->md5Sum = md5(json_encode($this->cacheListing));
    }

    /**
     * Save the cacheListing to memcached
     *
     * @return boolean
     */
    public function saveCacheListing() : bool
    {

        if (md5(json_encode($this->cacheListing)) === $this->md5Sum) {
            return false;
        }

        return parent::set('cacheListing', $this->cacheListing);
    }

    /**
     * a listing of all cached entries which have been
     * inserted through this wrapper
     *
     * @return boolean
     */
    public function listCache() : bool
    {

        $newlist = [];

        foreach ($this->cacheListing as $key => $val) {
            $newlist[$key] = new \stdClass();

            foreach ($val as $skey => $sval) {
                $newlist[$key]->{$skey} = $sval;
            }
        }

        $this->list = $newlist;

        return true;
    }


    /**
     * returns the current status
     */
    public function getStatus() : array
    {

        return [
            'status'        => parent::getStats(),
            'version'       => parent::getVersion(),
            'server_list'   => parent::getServerList()
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
    public function set(string $key, $value = null, $expiration = 0, &$udf_flags = NULL) : bool
    {

        if (parent::set($key, $value, ($expiration ? $expiration : null), $udf_flags)) {
            $expiration = empty($expiration) ? 0 : $expiration;

            // Prepare Listing
            $newListing = array(
                'key'        => (string)$key,
                'expiration' => (int)$expiration,
                'updated'    => (string)date('Y-m-d H:i:s'));

            if (isset($this->cacheListing [$key])) {
                $this->cacheListing [$key] = array_merge($this->cacheListing [$key], $newListing);
            } else {
                $newListing ['created']    = $newListing ['updated'];
                $this->cacheListing [$key] = $newListing;
            }

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
    public function get(string $key = null, $cache_cb = null, &$cas_token = null, &$udf_flags = NULL)
    {

        if (isset($this->cacheListing [$key])) {
            return parent::get($key, $cache_cb, $cas_token);
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

        if (parent::flush((int)$delay)) {
            $this->cacheListing = [];

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
    public function add(string $key, $value, int $expiration = null, &$udf_flags = NULL) : bool
    {
        return $this->set($key, $value, $expiration, $udf_flags);
    }

    /**
     * delete method
     *
     * @param $key  string
     * @param $time int
     *
     * @return boolean
     */
    public function delete(string $key, $time = 0) : bool
    {

        if (parent::delete($key, ($time ? $time : null))) {
            unset($this->cacheListing [$key]);
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

        if (parent::deleteByKey($server_key, $key, ($time ? $time : null))) {
            unset($this->cacheListing[$key]);
            $this->saveCacheListing();

            return true;
        }

        return false;
    }

    /**
     * delete memcached values based on an input array
     *
     *
     * @param array $keyArray
     * @return bool
     */
    public function deleteFromList(array $keyArray = []) : bool
    {

        if (!$keyArray) {
            return false;
        }

        foreach ($keyArray as $key_del) {
            if (!$this->cacheListing) {
                break;
            }

            foreach ($this->cacheListing as $key)
            {
                if (mb_strpos(mb_strtolower($key), mb_strtolower($key_del)) !== false) {
                    $this->delete($key);
                }
            }
        }

        return true;
    }
}
