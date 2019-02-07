<?php

namespace Rikudou\Tests;

use PHPUnit\Framework\TestCase;
use Rikudou\Exception\ReflectionException;
use Rikudou\ReflectionFile;
use Rikudou\Tests\Data\ClassWithEchoStatement;
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
        $this->assertFalse($this->getClassWithEchoStatementFile()->containsInlineHtml());
        $this->assertFalse($this->getFunctionsFile()->containsInlineHtml());
        $this->assertTrue($this->getInlineHtmlFile()->containsInlineHtml());
        $this->assertFalse($this->getNamespacedClassFile()->containsInlineHtml());
        $this->assertFalse($this->getNonNamespacedClassFile()->containsInlineHtml());
        $this->assertTrue($this->getNonPhpContentFile()->containsInlineHtml());
        $this->assertFalse($this->getOutputPrintingFile()->containsInlineHtml());
    }

    public function testContainsClass()
    {
        $this->assertTrue($this->getClassWithEchoStatementFile()->containsClass());
        $this->assertFalse($this->getFunctionsFile()->containsClass());
        $this->assertFalse($this->getInlineHtmlFile()->containsClass());
        $this->assertTrue($this->getNamespacedClassFile()->containsClass());
        $this->assertTrue($this->getNonNamespacedClassFile()->containsClass());
        $this->assertFalse($this->getNonPhpContentFile()->containsClass());
        $this->assertFalse($this->getOutputPrintingFile()->containsClass());
    }

    public function testGetNamespace()
    {
        $this->assertEquals('Rikudou\Tests\Data', $this->getClassWithEchoStatementFile()->getNamespace());
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
        $this->assertFalse($this->getClassWithEchoStatementFile()->printsOutput());
        $this->assertFalse($this->getFunctionsFile()->printsOutput());
        $this->assertTrue($this->getInlineHtmlFile()->printsOutput());
        $this->assertFalse($this->getNamespacedClassFile()->printsOutput());
        $this->assertFalse($this->getNonNamespacedClassFile()->printsOutput());
        $this->assertTrue($this->getNonPhpContentFile()->printsOutput());
        $this->assertTrue($this->getOutputPrintingFile()->printsOutput());
    }

    public function testContainsNamespace()
    {
        $this->assertTrue($this->getClassWithEchoStatementFile()->containsNamespace());
        $this->assertTrue($this->getFunctionsFile()->containsNamespace());
        $this->assertFalse($this->getInlineHtmlFile()->containsNamespace());
        $this->assertTrue($this->getNamespacedClassFile()->containsNamespace());
        $this->assertFalse($this->getNonNamespacedClassFile()->containsNamespace());
        $this->assertFalse($this->getNonPhpContentFile()->containsNamespace());
        $this->assertFalse($this->getOutputPrintingFile()->containsNamespace());
    }

    public function testContainsPhpCode()
    {
        $this->assertTrue($this->getClassWithEchoStatementFile()->containsPhpCode());
        $this->assertTrue($this->getFunctionsFile()->containsPhpCode());
        $this->assertTrue($this->getInlineHtmlFile()->containsPhpCode());
        $this->assertTrue($this->getNamespacedClassFile()->containsPhpCode());
        $this->assertTrue($this->getNonNamespacedClassFile()->containsPhpCode());
        $this->assertFalse($this->getNonPhpContentFile()->containsPhpCode());
        $this->assertTrue($this->getOutputPrintingFile()->containsPhpCode());
    }

    public function testGetClass()
    {
        $this->assertEquals(ClassWithEchoStatement::class, $this->getClassWithEchoStatementFile()->getClass()->getName());

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

    private function getClassWithEchoStatementFile()
    {
        return $this->getReflection('ClassWithEchoStatement.php');
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

    private function getReflection(string $file)
    {
        return new ReflectionFile(__DIR__ . "/Data/{$file}");
    }
}
