<?php

namespace Rikudou\Cache;

use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class SimpleFileCache implements CacheInterface
{
    /**
     * @var string|null
     */
    private $filePath = null;

    /**
     * @var int|null
     */
    private $modified = null;

    /**
     * @var string
     */
    private $directory;

    public function __construct(string $directory = null)
    {
        if (is_null($directory)) {
            $directory = sys_get_temp_dir() . '/rikudou-reflection-file-cache';
        }
        if (file_exists($directory)) {
            if (!is_dir($directory)) {
                throw new LogicException("The path '{$directory}' already exists and is not an directory");
            }
        } else {
            if (!@mkdir($directory, 0777, true)) {
                throw new LogicException("The path '{$directory}' does not exist and could not be created");
            }
        }
        $this->directory = $directory;
    }

    /**
     * This method is called at the very beginning and gives the class data to work on.
     *
     * @param string $filePath The absolute path to the file
     * @param int    $modified The unix timestamp of when the file was last modified
     *
     * @return SimpleFileCache
     */
    public function setData(string $filePath, int $modified)
    {
        $this->filePath = $filePath;
        $this->modified = $modified;

        return $this;
    }

    /**
     * Whether or not the cache has this file. If this returns false no other method is called.
     *
     * @return bool
     */
    public function isCached(): bool
    {
        return file_exists($this->getCachePath());
    }

    /**
     * Whether the cache is valid or not, the cache is invalidated if not.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->isCached()) {
            return false;
        }

        return filemtime($this->getCachePath()) >= $this->modified;
    }

    /**
     * This method should attempt to invalidate the cache.
     */
    public function invalidate(): void
    {
        @unlink($this->getCachePath());
    }

    /**
     * Returns instance of CachedData which contain information about the file
     *
     * @return CachedData
     */
    public function getCachedData(): CachedData
    {
        return require $this->getCachePath();
    }

    /**
     * Called to store the newly parsed data in cache
     *
     * @param CachedData $cachedData
     */
    public function store(CachedData $cachedData)
    {
        $content = "<?php\nreturn " . var_export($cachedData, true) . ';';

        $dir = dirname($this->getCachePath());
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new LogicException("The cache directory '{$dir}' does not exist and could not be created");
            }
        }

        file_put_contents($this->getCachePath(), $content);
    }

    /**
     * Clears all cached files
     */
    public function clearAll(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->directory
            )
        );

        foreach ($iterator as $file) {
            assert($file instanceof SplFileInfo);
            if ($file->isFile()) {
                unlink($file->getPathname());
            }
        }
    }

    /**
     * @return string
     */
    private function getCachePath(): string
    {
        return "{$this->directory}/{$this->filePath}";
    }
}
