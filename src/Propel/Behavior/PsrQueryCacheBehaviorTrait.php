<?php

namespace Serato\SwsApp\Propel\Behavior;

use Psr\Cache\CacheItemPoolInterface;

/**
 * PSR-6 Query Cache Behavior Trait
 *
 * A trait that provides a PSR-6 cache implementation for Propel database
 * models that implement the Query Cache behaviour.
 *
 * Models using this trait in concert with the Query Cache behaviour need to
 * call the public self::setCacheItemPool() method and pass it an instance of a
 * PSR-6 compatible cache pool.
 *
 * Models can be built with this trait by adding the following lines to their
 * XML schema's:
 *
 *       <behavior name="query_cache">
 *           <parameter name="backend" value="custom" />
 *           <parameter name="lifetime" value="600" />
 *       </behavior>
 *
 * See the Propel documentation for more information about the Query Cache
 * behavior:
 *
 * http://propelorm.org/documentation/behaviors/query-cache.html
 */
trait PsrQueryCacheBehaviorTrait
{
    /**
     * Confirms if the cache contains specified cache item.
     *
     * @param string    $key    Cache key
     * @return bool
     */
    public function cacheContains($key)
    {
        if ($this->getQueryCache() !== null) {
            return $this->getQueryCache()->hasItem($key);
        } else {
            return false;
        }
    }

    /**
     * Returns a Cache Item
     *
     * @param string    $key    Cache key
     * @return mixed
     */
    public function cacheFetch($key)
    {
        if ($this->getQueryCache() !== null) {
            $item = $this->getQueryCache()->getItem($key);
            if ($item->isHit()) {
                return $item->get();
            }
        }
    }

    /**
     * Stores an item into the cache
     *
     * @param string    $key        Cache key
     * @param mixed     $value      Item to cache
     * @param int       $lifetime   Cache TTL in seconds
     */
    public function cacheStore($key, $value, $lifetime = 600)
    {
        if ($this->getQueryCache() !== null) {
            $item = $this->getQueryCache()->getItem($key);
            $item->set($value);
            return $this->getQueryCache()->save($item);
        }
    }

    /**
     * Sets an instance of a PSR-6 cache item pool
     *
     * @param CacheItemPoolInterface  $cacheItemPool  PSR-6 cache item pool
     * @return $this
     */
    public function setQueryCache(CacheItemPoolInterface $cacheItemPool)
    {
        self::$cacheBackend = $cacheItemPool;
        return $this;
    }

    /**
     * Returns a cache backend
     *
     * @return null|CacheItemPoolInterface
     * @throws Exception
     */
    protected function getQueryCache()
    {
        return self::$cacheBackend;
    }
}
