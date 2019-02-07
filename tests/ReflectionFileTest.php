<?php

namespace Rikudou\Tests;

use PHPUnit\Framework\TestCase;
use Rikudou\Cache\CachedData;
use Rikudou\Cache\CacheInterface;
use Rikudou\Exception\ReflectionException;
use Rikudou\ReflectionFile;
use Rikudou\Tests\Data\AbstractClass;
use Rikudou\Tests\Data\ClassThatExtends;
use Rikudou\Tests\Data\ClassThatExtendsAndImplements;
use Rikudou\Tests\Data\ClassWithEchoStatement;
use Rikudou\Tests\Data\ClassWithInterface;
use Rikudou\Tests\Data\NamespacedClass;

class ReflectionFileTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        ob_start();
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                __DIR__ . '/Data'
            )
        );

        foreach ($files as $file) {
            assert($file instanceof \SplFileInfo);
            if ($file->isFile() && $file->getExtension() === 'php') {
                require_once $file->getRealPath();
            }
        }
        ob_clean();
    }

    public function testConstructor()
    {
        $this->expectException(ReflectionException::class);
        $this->getReflection('NonexistentFile.php');
    }

    public function testContainsInlineHtml()
    {
        $this->assertFalse($this->getAbstractClassFile()->containsInlineHtml());
        $this->assertFalse($this->getClassThatExtendsFile()->containsInlineHtml());
        $this->assertFalse($this->getClassThatExtendsAndImplementsFile()->containsInlineHtml());
        $this->assertFalse($this->getClassWithEchoStatementFile()->containsInlineHtml());
        $this->assertFalse($this->getClassWithInterfaceFile()->containsInlineHtml());
        $this->assertFalse($this->getFunctionsFile()->containsInlineHtml());
        $this->assertTrue($this->getInlineHtmlFile()->containsInlineHtml());
        $this->assertFalse($this->getNamespacedClassFile()->containsInlineHtml());
        $this->assertFalse($this->getNonNamespacedClassFile()->containsInlineHtml());
        $this->assertTrue($this->getNonPhpContentFile()->containsInlineHtml());
        $this->assertFalse($this->getOutputPrintingFile()->containsInlineHtml());
    }

    public function testContainsClass()
    {
        $this->assertTrue($this->getAbstractClassFile()->containsClass());
        $this->assertTrue($this->getClassThatExtendsFile()->containsClass());
        $this->assertTrue($this->getClassThatExtendsAndImplementsFile()->containsClass());
        $this->assertTrue($this->getClassWithEchoStatementFile()->containsClass());
        $this->assertTrue($this->getClassWithInterfaceFile()->containsClass());
        $this->assertFalse($this->getFunctionsFile()->containsClass());
        $this->assertFalse($this->getInlineHtmlFile()->containsClass());
        $this->assertTrue($this->getNamespacedClassFile()->containsClass());
        $this->assertTrue($this->getNonNamespacedClassFile()->containsClass());
        $this->assertFalse($this->getNonPhpContentFile()->containsClass());
        $this->assertFalse($this->getOutputPrintingFile()->containsClass());
    }

    public function testGetNamespace()
    {
        $this->assertEquals('Rikudou\Tests\Data', $this->getAbstractClassFile()->getNamespace());
        $this->assertEquals('Rikudou\Tests\Data', $this->getClassThatExtendsFile()->getNamespace());
        $this->assertEquals('Rikudou\Tests\Data', $this->getClassThatExtendsAndImplementsFile()->getNamespace());
        $this->assertEquals('Rikudou\Tests\Data', $this->getClassWithEchoStatementFile()->getNamespace());
        $this->assertEquals('Rikudou\Tests\Data', $this->getClassWithInterfaceFile()->getNamespace());
        $this->assertEquals('Rikudou\Tests\Data', $this->getFunctionsFile()->getNamespace());

        try {
            $this->getInlineHtmlFile()->getNamespace();
            $this->fail('Expected exception');
        } catch (ReflectionException $e) {
        }
        $this->assertEquals('Rikudou\Tests\Data', $this->getNamespacedClassFile()->getNamespace());

        try {
            $this->getNonNamespacedClassFile()->getNamespace();
            $this->fail('Expected exception');
        } catch (ReflectionException $e) {
        }

        try {
            $this->getNonPhpContentFile()->getNamespace();
            $this->fail('Expected exception');
        } catch (ReflectionException $e) {
        }

        try {
            $this->getOutputPrintingFile()->getNamespace();
            $this->fail('Expected exception');
        } catch (ReflectionException $e) {
        }
    }

    public function testPrintsOutput()
    {
        $this->assertFalse($this->getAbstractClassFile()->printsOutput());
        $this->assertFalse($this->getClassThatExtendsFile()->printsOutput());
        $this->assertFalse($this->getClassThatExtendsAndImplementsFile()->printsOutput());
        $this->assertFalse($this->getClassWithEchoStatementFile()->printsOutput());
        $this->assertFalse($this->getClassWithInterfaceFile()->printsOutput());
        $this->assertFalse($this->getFunctionsFile()->printsOutput());
        $this->assertTrue($this->getInlineHtmlFile()->printsOutput());
        $this->assertFalse($this->getNamespacedClassFile()->printsOutput());
        $this->assertFalse($this->getNonNamespacedClassFile()->printsOutput());
        $this->assertTrue($this->getNonPhpContentFile()->printsOutput());
        $this->assertTrue($this->getOutputPrintingFile()->printsOutput());
    }

    public function testContainsNamespace()
    {
        $this->assertTrue($this->getAbstractClassFile()->containsNamespace());
        $this->assertTrue($this->getClassThatExtendsFile()->containsNamespace());
        $this->assertTrue($this->getClassThatExtendsAndImplementsFile()->containsNamespace());
        $this->assertTrue($this->getClassWithEchoStatementFile()->containsNamespace());
        $this->assertTrue($this->getClassWithInterfaceFile()->containsNamespace());
        $this->assertTrue($this->getFunctionsFile()->containsNamespace());
        $this->assertFalse($this->getInlineHtmlFile()->containsNamespace());
        $this->assertTrue($this->getNamespacedClassFile()->containsNamespace());
        $this->assertFalse($this->getNonNamespacedClassFile()->containsNamespace());
        $this->assertFalse($this->getNonPhpContentFile()->containsNamespace());
        $this->assertFalse($this->getOutputPrintingFile()->containsNamespace());
    }

    public function testContainsPhpCode()
    {
        $this->assertTrue($this->getAbstractClassFile()->containsPhpCode());
        $this->assertTrue($this->getClassThatExtendsFile()->containsPhpCode());
        $this->assertTrue($this->getClassThatExtendsAndImplementsFile()->containsPhpCode());
        $this->assertTrue($this->getClassWithEchoStatementFile()->containsPhpCode());
        $this->assertTrue($this->getClassWithInterfaceFile()->containsPhpCode());
        $this->assertTrue($this->getFunctionsFile()->containsPhpCode());
        $this->assertTrue($this->getInlineHtmlFile()->containsPhpCode());
        $this->assertTrue($this->getNamespacedClassFile()->containsPhpCode());
        $this->assertTrue($this->getNonNamespacedClassFile()->containsPhpCode());
        $this->assertFalse($this->getNonPhpContentFile()->containsPhpCode());
        $this->assertTrue($this->getOutputPrintingFile()->containsPhpCode());
    }

    public function testGetClass()
    {
        $this->assertEquals(AbstractClass::class, $this->getAbstractClassFile()->getClass()->getName());
        $this->assertEquals(ClassThatExtends::class, $this->getClassThatExtendsFile()->getClass()->getName());
        $this->assertEquals(ClassThatExtendsAndImplements::class, $this->getClassThatExtendsAndImplementsFile()->getClass()->getName());
        $this->assertEquals(ClassWithEchoStatement::class, $this->getClassWithEchoStatementFile()->getClass()->getName());
        $this->assertEquals(ClassWithInterface::class, $this->getClassWithInterfaceFile()->getClass()->getName());

        try {
            $this->getFunctionsFile()->getClass();
            $this->fail('Expected exception');
        } catch (ReflectionException $e) {
        }

        try {
            $this->getInlineHtmlFile()->getClass();
            $this->fail('Expected exception');
        } catch (ReflectionException $e) {
        }
        $this->assertEquals(NamespacedClass::class, $this->getNamespacedClassFile()->getClass()->getName());
        $this->assertEquals(\NonNamespacedClass::class, $this->getNonNamespacedClassFile()->getClass()->getName());

        try {
            $this->getNonPhpContentFile()->getClass();
            $this->fail('Expected exception');
        } catch (ReflectionException $e) {
        }

        try {
            $this->getOutputPrintingFile()->getClass();
            $this->fail('Expected exception');
        } catch (ReflectionException $e) {
        }
    }

    public function testContainsFunctions()
    {
        $this->assertFalse($this->getClassWithEchoStatementFile()->containsFunctions());
        $this->assertTrue($this->getFunctionsFile()->containsFunctions());
        $this->assertFalse($this->getInlineHtmlFile()->containsFunctions());
        $this->assertFalse($this->getNamespacedClassFile()->containsFunctions());
        $this->assertFalse($this->getNonNamespacedClassFile()->containsFunctions());
        $this->assertFalse($this->getNonPhpContentFile()->containsFunctions());
        $this->assertFalse($this->getOutputPrintingFile()->containsFunctions());
    }

    public function testGetFunctions()
    {
        $this->assertEquals([], $this->getClassWithEchoStatementFile()->getFunctions());

        $functions = $this->getFunctionsFile()->getFunctions();
        $this->assertNotEmpty($functions);
        $this->assertCount(2, $functions);
        $this->assertContainsOnlyInstancesOf(\ReflectionFunction::class, $functions);
        foreach ($functions as $function) {
            $this->assertContains($function->getName(), [
                'Rikudou\Tests\Data\myFunction1',
                'Rikudou\Tests\Data\myFunction2',
            ]);
        }

        $this->assertEquals([], $this->getInlineHtmlFile()->getFunctions());
        $this->assertEquals([], $this->getNamespacedClassFile()->getFunctions());
        $this->assertEquals([], $this->getNonNamespacedClassFile()->getFunctions());
        $this->assertEquals([], $this->getNonPhpContentFile()->getFunctions());
        $this->assertEquals([], $this->getOutputPrintingFile()->getFunctions());
    }

    public function testCache()
    {
        $cachedData = new CachedData();
        $cachedData->setClass('NamespacedClass');
        $cachedData->setNamespace('Rikudou\Tests\Data');
        $cachedData->setFunctions([
            'myFunction1',
        ]);
        $cache = $this->getFakeCache($cachedData);

        // adds namespace to functions, for checking if the correct functions
        // are returned
        $namespacedFunctions = function () use ($cachedData) {
            $result = [];
            foreach ($cachedData->getFunctions() as $function) {
                $result[] = ($cachedData->getNamespace() ?? '') . "\\{$function}";
            }

            return $result;
        };

        // new instance will be created every time to reflect the changes made to $cache
        $newInstance = function () use (&$cache) {
            return $this->getReflection('AbstractClass.php', $cache);
        };
        $instance = $newInstance();

        // all stuff should be from cache
        $this->assertEquals($cachedData->containsInlineHtml(), $instance->containsInlineHtml());
        $this->assertEquals($cachedData->containsPhpCode(), $instance->containsPhpCode());
        $this->assertEquals($cachedData->printsOutput(), $instance->printsOutput());
        $this->assertEquals($cachedData->getNamespace() . '\\' . $cachedData->getClass(), $instance->getClass()->getName());
        $this->assertEquals(NamespacedClass::class, $instance->getClass()->getName());
        $this->assertEquals(count($cachedData->getFunctions()), count($instance->getFunctions()));
        foreach ($instance->getFunctions() as $function) {
            $this->assertContains($function->getName(), $namespacedFunctions());
        }

        $cache->isValid = false;
        // it should not try to load the data anymore, so even if the cache is set to invalid it should still return
        // fake data
        $this->assertEquals(NamespacedClass::class, $instance->getClass()->getName());

        // reset
        $cache->attemptedToInvalidate = false;
        $cache->attemptedToStore = false;

        // now the correct data should be returned as the cache is set to not valid
        $instance = $newInstance();
        $this->assertEquals(AbstractClass::class, $instance->getClass()->getName());

        // the real parsed result should be returned if the current item is not cached yet
        $cache->isValid = true;
        $cache->isCached = false;
        $instance = $newInstance();
        $this->assertEquals(AbstractClass::class, $instance->getClass()->getName());
        // if the file is not cached, the reflection should attempt to store it
        // but it shouldn't attempt to invalidate as there is nothing to invalidate
        $this->assertTrue($cache->attemptedToStore);
        $this->assertFalse($cache->attemptedToInvalidate);

        $cache->isCached = true;
        $cache->isValid = false;
        $instance = $newInstance();
        $this->assertEquals(AbstractClass::class, $instance->getClass()->getName());
        // if the data is cached but not valid, it should attempt to invalidate
        // after the data is parsed from file it should try to store them in cache
        $this->assertTrue($cache->attemptedToInvalidate);
        $this->assertTrue($cache->attemptedToStore);
    }

    private function getAbstractClassFile()
    {
        return $this->getReflection('AbstractClass.php');
    }

    private function getClassThatExtendsFile()
    {
        return $this->getReflection('ClassThatExtends.php');
    }

    private function getClassThatExtendsAndImplementsFile()
    {
        return $this->getReflection('ClassThatExtendsAndImplements.php');
    }

    private function getClassWithEchoStatementFile()
    {
        return $this->getReflection('ClassWithEchoStatement.php');
    }

    private function getClassWithInterfaceFile()
    {
        return $this->getReflection('ClassWithInterface.php');
    }

    private function getFunctionsFile()
    {
        return $this->getReflection('FunctionsFile.php');
    }

    private function getInlineHtmlFile()
    {
        return $this->getReflection('InlineHtmlFile.php');
    }

    private function getNamespacedClassFile()
    {
        return $this->getReflection('NamespacedClass.php');
    }

    private function getNonNamespacedClassFile()
    {
        return $this->getReflection('NonNamespacedClass.php');
    }

    private function getNonPhpContentFile()
    {
        return $this->getReflection('NonPhpContent.php');
    }

    private function getOutputPrintingFile()
    {
        return $this->getReflection('OutputPrintingFile.php');
    }

    private function getReflection(string $file, CacheInterface $cache = null)
    {
        return new ReflectionFile(__DIR__ . "/Data/{$file}", $cache);
    }

    private function getFakeCache(?CachedData $initialData = null)
    {
        return new class($initialData) implements CacheInterface {
            /**
             * @var string
             */
            public $filePath;

            /**
             * @var int
             */
            public $modified;

            /**
             * @var bool
             */
            public $isCached = true;

            /**
             * @var bool
             */
            public $isValid = true;

            /**
             * @var CachedData;
             */
            public $cachedData;

            /**
             * @var bool
             */
            public $attemptedToStore = false;

            /**
             * @var bool
             */
            public $attemptedToInvalidate = false;

            public function __construct(CachedData $cachedData)
            {
                if (is_null($cachedData)) {
                    $cachedData = new CachedData();
                }
                $this->cachedData = $cachedData;
            }

            /**
             * This method is called at the very beginning and gives the class data to work on.
             *
             * @param string $filePath The absolute path to the file
             * @param int    $modified The unix timestamp of when the file was last modified
             */
            public function setData(string $filePath, int $modified)
            {
                $this->filePath = $filePath;
                $this->modified = $modified;
            }

            /**
             * Whether or not the cache has this file. If this returns false no other method is called.
             *
             * @return bool
             */
            public function isCached(): bool
            {
                return $this->isCached;
            }

            /**
             * Whether the cache is valid or not, the cache is invalidated if not.
             *
             * @return bool
             */
            public function isValid(): bool
            {
                return $this->isValid;
            }

            /**
             * This method should attempt to invalidate the cache.
             */
            public function invalidate(): void
            {
                $this->attemptedToInvalidate = true;
            }

            /**
             * Returns instance of CachedData which contain information about the file
             *
             * @return CachedData
             */
            public function getCachedData(): CachedData
            {
                return $this->cachedData;
            }

            /**
             * Called to store the newly parsed data in cache
             *
             * @param CachedData $cachedData
             */
            public function store(CachedData $cachedData)
            {
                $this->attemptedToStore = true;
            }
        };
    }
}
