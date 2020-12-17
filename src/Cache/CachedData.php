<?php

namespace Rikudou\Cache;

final class CachedData
{
    /**
     * @var bool
     */
    private $containsInlineHtml = false;

    /**
     * @var bool
     */
    private $containsPhpCode = false;

    /**
     * @var bool
     */
    private $printsOutput = false;

    /**
     * @var string|null
     */
    private $class = null;

    /**
     * @var string|null
     */
    private $namespace = null;

    /**
     * @var string[]
     */
    private $functions = [];

    /**
     * @param array<string, mixed> $an_array
     *
     * @internal
     *
     * @return CachedData
     */
    public static function __set_state($an_array)
    {
        $instance = new static();

        foreach ($an_array as $propertyName => $value) {
            if (property_exists($instance, $propertyName)) {
                $instance->{$propertyName} = $value;
            }
        }

        return $instance;
    }

    /**
     * @return bool
     */
    public function containsInlineHtml(): bool
    {
        return $this->containsInlineHtml;
    }

    /**
     * @return bool
     */
    public function containsPhpCode(): bool
    {
        return $this->containsPhpCode;
    }

    /**
     * @return bool
     */
    public function printsOutput(): bool
    {
        return $this->printsOutput;
    }

    /**
     * @return string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @return string[]
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @param bool $containsInlineHtml
     *
     * @return CachedData
     */
    public function setContainsInlineHtml(bool $containsInlineHtml): CachedData
    {
        $this->containsInlineHtml = $containsInlineHtml;

        return $this;
    }

    /**
     * @param bool $containsPhpCode
     *
     * @return CachedData
     */
    public function setContainsPhpCode(bool $containsPhpCode): CachedData
    {
        $this->containsPhpCode = $containsPhpCode;

        return $this;
    }

    /**
     * @param bool $printsOutput
     *
     * @return CachedData
     */
    public function setPrintsOutput(bool $printsOutput): CachedData
    {
        $this->printsOutput = $printsOutput;

        return $this;
    }

    /**
     * @param string|null $class
     *
     * @return CachedData
     */
    public function setClass(?string $class): CachedData
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @param string|null $namespace
     *
     * @return CachedData
     */
    public function setNamespace(?string $namespace): CachedData
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @param string[] $functions
     *
     * @return CachedData
     */
    public function setFunctions(iterable $functions): CachedData
    {
        foreach ($functions as $function) {
            $this->addFunction($function);
        }

        return $this;
    }

    /**
     * @param string $function
     *
     * @return CachedData
     */
    public function addFunction(string $function): CachedData
    {
        $this->functions[] = $function;

        return $this;
    }
}
