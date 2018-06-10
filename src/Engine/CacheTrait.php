<?php
declare(strict_types=1);
namespace chilimatic\lib\Cache\Engine;

/**
 * Class CacheTrait
 *
 * @package chilimatic\lib\cache
 */
trait CacheTrait
{

    /**
     * storage for key flushing, if needed
     *
     * @var array
     */
    private $entryMetaData = [];

    /**
     * md5 sum of the cache_list from
     * the contructor
     *
     * @var string
     */
    protected $md5Sum;

    /**
     * connected boolean
     *
     * @var bool
     */
    protected $connected = false;

    /**
     * list array of all entries
     *
     * @var array
     */
    protected $list = [];

    /**
     * @return bool
     */
    public function isConnected() : bool
    {
        return (bool) $this->connected;
    }

    /**
     * @param bool $connected
     */
    protected function setConnected(bool $connected = true): void
    {
        $this->connected = $connected;
    }

    /**
     * @param string $key
     * @param int $expiration
     */
    private function updateMetaData(string $key, int $expiration): void
    {
        $expiration = empty($expiration) ? 0 : $expiration;

        $newListing = [
            'key'        => $key,
            'expiration' => $expiration,
            'updated'    => (string)date('Y-m-d H:i:s')];

        if (isset($this->entryMetaData[$key])) {
            $this->entryMetaData[$key] = array_merge($this->entryMetaData[$key], $newListing);
        } else {
            $newListing['created']    = $newListing ['updated'];
            $this->entryMetaData[$key] = $newListing;
        }
    }

    /**
     * delete cached values based on an input array
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
            if (!$this->entryMetaData) {
                break;
            }

            foreach ($this->entryMetaData as $key) {
                if (stripos($key, strtolower($key_del) !== false)) {
                    $this->delete($key);
                }
            }
        }

        return true;
    }

}