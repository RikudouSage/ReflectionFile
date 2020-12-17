<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 7.2.19
 * Time: 18:21
 */

namespace Rikudou\Tests;

use PHPUnit\Framework\TestCase;
use Rikudou\Cache\CachedData;
use Rikudou\Cache\SimpleFileCache;

class SimpleFileCacheTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        (new SimpleFileCache(self::getCacheDirectory()))->clearAll();
    }

    public static function tearDownAfterClass(): void
    {
        self::setUpBeforeClass();
        @unlink(sys_get_temp_dir() . '/rikudou-reflection-random-directory');
    }

    public function testIsValid()
    {
        $instance = $this->getInstance();
        $this->assertTrue($instance->isValid());

        // invalid for uncached files
        $instance->setData(__DIR__ . '/Data/ClassThatExtends.php', strtotime('-1 year'));
        $this->assertFalse($instance->isValid());

        // invalid if the modified time is newer than the stored cache time
        $instance->setData($this->getDefaultFile(), time());
        $this->assertFalse($instance->isValid());
    }

    public function testIsCached()
    {
        $instance = $this->getInstance();
        $this->assertTrue($instance->isCached());

        // false for uncached files
        $instance->setData(__DIR__ . '/Data/ClassThatExtends.php', time());
        $this->assertFalse($instance->isCached());

        // should ignore that the file is modified, just checks whether the cache exists
        $instance->setData($this->getDefaultFile(), time());
        $this->assertTrue($instance->isCached());
    }

    public function testInvalidate()
    {
        $instance = $this->getInstance();
        $this->assertTrue($instance->isCached());

        $instance->invalidate();
        $this->assertFalse($instance->isCached());
    }

    public function testSetData()
    {
        $instance = $this->getInstance();

        $this->assertTrue($instance->isCached());
        $this->assertTrue($instance->isValid());

        $instance->setData($this->getDefaultFile(), time());
        $this->assertTrue($instance->isCached());
        $this->assertFalse($instance->isValid());

        $instance->setData(__DIR__ . '/Data/ClassThatExtends.php', time());
        $this->assertFalse($instance->isCached());
        $this->assertFalse($instance->isValid());
    }

    public function testGetCachedData()
    {
        $instance = $this->getInstance();
        $this->assertInstanceOf(CachedData::class, $instance->getCachedData());
    }

    public function testStore()
    {
        $instance = $this->getInstance();
        $instance->setData(__DIR__ . '/Data/ClassWithInterface.php', filemtime(__DIR__ . '/Data/ClassWithInterface.php'));

        $this->assertFalse($instance->isCached());
        $instance->store(new CachedData());

        $this->assertTrue($instance->isCached());
        $this->assertTrue($instance->isValid());
    }

    public function test__construct()
    {
        $directory = function (string $dir): string {
            return sys_get_temp_dir() . "/simple-file-cache-test/{$dir}";
        };
        @rmdir($directory('test1'));
        @rmdir($directory('test2'));

        new SimpleFileCache($directory('test1'));
        $this->assertTrue(file_exists($directory('test1')), 'The directory does not exist');
        new SimpleFileCache($directory('test2'));
        $this->assertTrue(file_exists($directory('test2')), 'The directory does not exist');
        new SimpleFileCache();
        $this->assertTrue(file_exists(sys_get_temp_dir() . '/rikudou-reflection-file-cache'));

        rmdir($directory('test1'));
        rmdir($directory('test2'));
    }

    public function test__constructExistingFile()
    {
        $dir = sys_get_temp_dir() . '/rikudou-reflection-random-directory';
        $this->expectException(\LogicException::class);
        touch($dir);
        new SimpleFileCache($dir);
    }

    public function test__constructReadonlyDirectory()
    {
        if (!file_exists('/dev/null')) {
            $this->markTestSkipped('/dev/null does not exist, skipping test');
        }
        $this->expectException(\LogicException::class);
        new SimpleFileCache('/dev/null/my-directory');
    }

    public function testClearAll()
    {
        $instance = $this->getInstance();
        $this->assertTrue($instance->isCached());

        $instance->clearAll();
        $this->assertFalse($instance->isCached());
    }

    private function getInstance(): SimpleFileCache
    {
        $instance = new SimpleFileCache(self::getCacheDirectory());
        $instance->setData($this->getDefaultFile(), strtotime('-1 week'));
        // cache it only once so that the cache is not valid all the time
        if (!$instance->isCached()) {
            $instance->store(new CachedData());
            // sleep to have cache that was not modified at this exact time
            sleep(2);
        }

        return $instance;
    }

    private function getDefaultFile(): string
    {
        return __DIR__ . '/Data/AbstractClass.php';
    }

    private static function getCacheDirectory(): string
    {
        return sys_get_temp_dir() . '/rikudou-reflection-file-tests';
    }
}
