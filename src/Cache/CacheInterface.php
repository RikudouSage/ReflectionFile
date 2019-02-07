<?php

namespace Rikudou\Cache;

interface CacheInterface
{
    /**
     * This method is called at the very beginning and gives the class data to work on.
     *
     * @param string $filePath The absolute path to the file
     * @param int    $modified The unix timestamp of when the file was last modified
     */
    public function setData(string $filePath, int $modified);

    /**
     * Whether or not the cache has this file. If this returns false no other method is called.
     *
     * @return bool
     */
    public function isCached(): bool;

    /**
     * Whether the cache is valid or not, the cache is invalidated if not.
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * This method should attempt to invalidate the cache.
     */
    public function invalidate(): void;

    /**
     * Returns instance of CachedData which contain information about the file
     *
     * @return CachedData
     */
    public function getCachedData(): CachedData;

    /**
     * Called to store the newly parsed data in cache
     *
     * @param CachedData $cachedData
     */
    public function store(CachedData $cachedData);

    /**
     * Clears all cached files
     */
    public function clearAll(): void;
}
