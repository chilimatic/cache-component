<?php
declare(strict_types=1);
namespace chilimatic\lib\Cache\Engine;

use chilimatic\lib\Cache\Engine\Adapter\Memory;
use chilimatic\lib\Cache\Exception\CacheException;

/**
 * Class CacheFactory
 *
 * @package chilimatic\lib\cache
 */
class CacheFactory
{

    /**
     * Error Number
     *
     * @var string
     */
    public const ERROR_CACHE_MISSING = 1;

    /**
     * Init like always enables to reset the class
     *
     * @param string $cacheClassName
     * @param array $cacheClassParam
     *
     * @throws CacheException|\Exception
     *
     * @return mixed
     */
    public static function make(string $cacheClassName, array $cacheClassParam = [])
    {
        if (!$cacheClassName) {
            throw new CacheException('name is empty');
        }

        try {
            // check if the class exists or can be loaded
            if (!class_exists($cacheClassName, true)) {
                throw new CacheException('The Cache is not implemented or not installed: ' . $cacheClassName, self::ERROR_CACHE_MISSING, E_USER_ERROR, __FILE__, __LINE__);
            }

            /**
             * @var CacheTrait $cache
             */
            $cache = new $cacheClassName($cacheClassParam);

            if ($cache->isConnected() === false) {
                throw new CacheException('The Cache could not establish connection: ' . $cacheClassName, self::ERROR_CACHE_MISSING, E_USER_ERROR, __FILE__, __LINE__);
            }

        } catch (CacheException $e) {
            throw $e;
        }

        return $cache;
    }
}