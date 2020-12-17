<?php

namespace Rikudou\Tests;

use PHPUnit\Framework\TestCase;
use Rikudou\Cache\CachedData;

class CachedDataTest extends TestCase
{
    public function testSetPrintsOutput()
    {
        $instance = $this->getInstance();
        $instance->setPrintsOutput(true);
        $this->assertEquals(true, $instance->printsOutput());
        $instance->setPrintsOutput(false);
        $this->assertEquals(false, $instance->printsOutput());
    }

    public function testContainsPhpCode()
    {
        $instance = $this->getInstance();
        $this->assertEquals(false, $instance->containsPhpCode());
        $instance->setContainsPhpCode(true);
        $this->assertEquals(true, $instance->containsPhpCode());
    }

    public function testPrintsOutput()
    {
        $instance = $this->getInstance();
        $this->assertEquals(false, $instance->printsOutput());
        $instance->setPrintsOutput(true);
        $this->assertEquals(true, $instance->printsOutput());
    }

    public function testSetContainsInlineHtml()
    {
        $instance = $this->getInstance();
        $instance->setContainsInlineHtml(true);
        $this->assertEquals(true, $instance->containsInlineHtml());
        $instance->setContainsInlineHtml(false);
        $this->assertEquals(false, $instance->containsInlineHtml());
    }

    public function testContainsInlineHtml()
    {
        $instance = $this->getInstance();
        $this->assertEquals(false, $instance->containsInlineHtml());
        $instance->setContainsInlineHtml(true);
        $this->assertEquals(true, $instance->containsInlineHtml());
    }

    public function testSetNamespace()
    {
        $instance = $this->getInstance();
        $instance->setNamespace('Test');
        $this->assertEquals('Test', $instance->getNamespace());
        $instance->setNamespace('Rikudou\Tests\Data');
        $this->assertEquals('Rikudou\Tests\Data', $instance->getNamespace());
        $instance->setNamespace(null);
        $this->assertEquals(null, $instance->getNamespace());
    }

    public function testSetFunctions()
    {
        $instance = $this->getInstance();
        $instance->setFunctions([
            'test1',
        ]);
        $this->assertEquals([
            'test1',
        ], $instance->getFunctions());
    }

    public function testSetClass()
    {
        $instance = $this->getInstance();
        $instance->setClass('Test');
        $this->assertEquals('Test', $instance->getClass());
        $instance->setClass('Rikudou\Tests\Data\AbstractClass');
        $this->assertEquals('Rikudou\Tests\Data\AbstractClass', $instance->getClass());
        $instance->setClass(null);
        $this->assertEquals(null, $instance->getClass());
    }

    public function testGetNamespace()
    {
        $instance = $this->getInstance();
        $this->assertEquals(null, $instance->getNamespace());
        $instance->setNamespace('Test');
        $this->assertEquals('Test', $instance->getNamespace());
    }

    public function testAddFunction()
    {
        $instance = $this->getInstance();
        $instance->addFunction('test');
        $this->assertEquals([
            'test',
        ], $instance->getFunctions());
        $instance->addFunction('test2');
        $this->assertEquals([
            'test',
            'test2',
        ], $instance->getFunctions());
    }

    public function testSetContainsPhpCode()
    {
        $instance = $this->getInstance();
        $instance->setContainsPhpCode(true);
        $this->assertEquals(true, $instance->containsPhpCode());
        $instance->setContainsPhpCode(false);
        $this->assertEquals(false, $instance->containsPhpCode());
    }

    public function test__set_state()
    {
        $object = CachedData::__set_state([
            'containsInlineHtml' => true,
            'printsOutput' => true,
        ]);

        $this->assertEquals(true, $object->containsInlineHtml());
        $this->assertEquals(true, $object->printsOutput());
        $this->assertEquals(false, $object->containsPhpCode());
        $this->assertEquals([], $object->getFunctions());
        $this->assertEquals(null, $object->getClass());
        $this->assertEquals(null, $object->getNamespace());

        $instance = $this->getInstance();
        $instance->setClass('Test');
        $instance->setNamespace('Test2');
        $instance->setContainsPhpCode(true);
        $instance->setPrintsOutput(true);

        $exported = var_export($instance, true);

        /** @var CachedData $recovered */
        $recovered = eval("return ${exported};");
        $this->assertInstanceOf(CachedData::class, $recovered);

        $this->assertEquals($instance->containsPhpCode(), $recovered->containsPhpCode());
        $this->assertEquals($instance->containsInlineHtml(), $recovered->containsInlineHtml());
        $this->assertEquals($instance->printsOutput(), $recovered->printsOutput());
        $this->assertEquals($instance->getClass(), $recovered->getClass());
        $this->assertEquals($instance->getNamespace(), $recovered->getNamespace());
        $this->assertEquals($instance->getFunctions(), $recovered->getFunctions());
    }

    public function testGetClass()
    {
        $instance = $this->getInstance();
        $this->assertEquals(null, $instance->getClass());
        $instance->setClass('Test');
        $this->assertEquals('Test', $instance->getClass());
    }

    public function testGetFunctions()
    {
        $instance = $this->getInstance();
        $this->assertIsArray($instance->getFunctions());
        $this->assertEmpty($instance->getFunctions());
        $instance->addFunction('Test');
        $this->assertEquals([
            'Test',
        ], $instance->getFunctions());
    }

    private function getInstance()
    {
        return new CachedData();
    }
}
