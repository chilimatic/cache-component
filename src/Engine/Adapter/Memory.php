<?php
declare(strict_types=1);

namespace chilimatic\lib\Cache\Engine\Adapter;

use chilimatic\lib\Cache\Engine\CacheTrait;
use chilimatic\lib\Cache\Engine\ICache;


class Memory implements ICache
{
    use CacheTrait;

    /**
     * @var int
     */
    private $removeCount = 0;

    /**
     * @var int
     */
    private $writeCount = 0;

    /**
     * @var int
     */
    private $readCount = 0;

    /**
     * @var array
     */
    private $store = [];

    /**
     * @param \stdClass $param
     */
    public function __construct($param = null){
        $this->setConnected(true);
    }

    /**
     * @return boolean
     */
    public function saveCacheListing() : bool
    {
        return true;
    }

    /**
     * returns the current status
     */
    public function getStatus() : array
    {
        return [
            'status'        => [
                'entries-stored' => \count($this->store),
                'accessed'       => $this->readCount,
                'removed'        => $this->removeCount,
                'written'        => $this->writeCount,
            ],
        ];
    }

    /**
     * @param $key        string
     * @param $value      mixed
     * @param $expiration int
     *
     * @return boolean
     */
    public function set(string $key, $value = null, int $expiration = 0) : bool
    {

        $this->updateMetaData($key, $expiration);
        $this->store[$key] = $value;

        $this->writeCount++;

        return true;
    }

    /**
     * @param string|null $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $this->readCount++;
        if (isset($this->entryMetaData[$key])) {
            return $this->store[$key];
        }

        return null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->store);
    }

    /**
     * @param int $delay
     * @return bool
     */
    public function flush(int $delay = 0) : bool
    {
        $this->entryMetaData = [];
        $this->store = [];

        return true;
    }

    /**
     * @param string $key
     * @param $value
     * @param int|null $expiration
     * @return bool
     */
    public function add(string $key, $value, int $expiration = null) : bool
    {
        return $this->set($key, $value, $expiration);
    }

    /**
     * @param $key  string
     * @param $time int
     *
     * @return boolean
     */
    public function delete(string $key, int $time = 0) : bool
    {
        $this->removeCount++;
        unset($this->entryMetaData[$key], $this->store[$key]);
        return true;
    }

    /**
     * @return int
     */
    public function getRemoveCount(): int
    {
        return $this->removeCount;
    }

    /**
     * @return int
     */
    public function getWriteCount(): int
    {
        return $this->writeCount;
    }

    /**
     * @return int
     */
    public function getReadCount(): int
    {
        return $this->readCount;
    }

    /**
     * @return array
     */
    public function getStore(): array
    {
        return $this->store;
    }
}
