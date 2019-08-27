<?php

namespace Rikudou;

use Rikudou\Cache\CachedData;
use Rikudou\Cache\CacheInterface;
use Rikudou\Exception\ReflectionException;
use Rikudou\Parser\Token;

final class ReflectionFile
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var bool
     */
    private $parsed = false;

    /**
     * @var bool
     */
    private $inlineHtml = false;

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
     * @var array
     */
    private $functions = [];

    /**
     * @var CacheInterface|null
     */
    private $cache;

    /**
     * @param string              $file
     * @param CacheInterface|null $cache
     *
     * @throws ReflectionException
     */
    public function __construct(string $file, ?CacheInterface $cache = null)
    {
        if (!defined('T_UNKNOWN')) {
            require_once __DIR__ . '/../globals.php';
        }
        if (!file_exists($file)) {
            throw new ReflectionException("The file '{$file}' does not exist");
        }
        $this->file = $file;
        $this->cache = $cache;
    }

    /**
     * @throws ReflectionException
     * @throws \ReflectionException
     *
     * @return \ReflectionClass
     */
    public function getClass(): \ReflectionClass
    {
        $this->parse();
        if (!$this->containsClass()) {
            throw new ReflectionException('The file does not contain a class');
        }
        $class = '';
        if ($this->containsNamespace()) {
            $class .= $this->getNamespace() . '\\';
        }
        $class .= $this->class;

        return new \ReflectionClass($class);
    }

    /**
     * @return bool
     */
    public function containsClass(): bool
    {
        $this->parse();

        return !is_null($this->class);
    }

    /**
     * @throws ReflectionException
     *
     * @return string
     */
    public function getNamespace(): string
    {
        $this->parse();
        if (!$this->containsNamespace()) {
            throw new ReflectionException('The file does not contain a namespace');
        }

        assert(is_string($this->namespace));

        return $this->namespace;
    }

    /**
     * @return bool
     */
    public function containsNamespace(): bool
    {
        $this->parse();

        return !is_null($this->namespace);
    }

    /**
     * @return bool
     */
    public function containsInlineHtml(): bool
    {
        $this->parse();

        return $this->inlineHtml;
    }

    /**
     * @return bool
     */
    public function containsPhpCode(): bool
    {
        $this->parse();

        return $this->containsPhpCode;
    }

    /**
     * @return bool
     */
    public function printsOutput(): bool
    {
        $this->parse();

        return $this->printsOutput;
    }

    /**
     * @throws \ReflectionException
     *
     * @return \ReflectionFunction[]
     */
    public function getFunctions(): array
    {
        $this->parse();
        $result = [];
        foreach ($this->functions as $function) {
            if ($this->containsNamespace()) {
                $function = $this->getNamespace() . '\\' . $function;
            }
            $result[] = new \ReflectionFunction($function);
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function containsFunctions(): bool
    {
        $this->parse();

        return !!count($this->functions);
    }

    private function parse()
    {
        if (!$this->parsed) {
            if (!is_null($this->cache)) {
                $this->cache->setData($this->file, filemtime($this->file) ?: 0);
                if ($this->cache->isCached()) {
                    if ($this->cache->isValid()) {
                        $cachedData = $this->cache->getCachedData();
                        $this->inlineHtml = $cachedData->containsInlineHtml();
                        $this->containsPhpCode = $cachedData->containsPhpCode();
                        $this->printsOutput = $cachedData->printsOutput();
                        $this->class = $cachedData->getClass();
                        $this->namespace = $cachedData->getNamespace();
                        $this->functions = $cachedData->getFunctions();

                        $this->parsed = true;

                        return;
                    } else {
                        $this->cache->invalidate();
                    }
                }
            }

            $content = file_get_contents($this->file);
            assert(is_string($content));

            $modes = [
                'none' => 1 << 0,
                'classParsing' => 1 << 1,
                'namespaceParsing' => 1 << 2,
                'insideClass' => 1 << 3,
                'insideFunction' => 1 << 4,
                'functionParsing' => 1 << 5,
                'extendsImplements' => 1 << 6,
                'importParsing' => 1 << 7,
            ];

            $currentMode = $modes['none'];

            $braces = [
                'opening' => 0,
                'closing' => 0,
            ];

            $functionName = '';

            $tokens = token_get_all($content);
            $previousToken = function (int $step = 1) use (&$index, $tokens): Token {
                return new Token($tokens[$index - $step]);
            };
            foreach ($tokens as $index => $token) {
                $token = new Token($token);

                if ($currentMode & $modes['none']) {
                    switch ($token->getType()) {
                        case T_INLINE_HTML:
                            $this->inlineHtml = true;
                            $this->printsOutput = true;
                            break;
                        case T_OPEN_TAG:
                            $this->containsPhpCode = true;
                            break;
                        case T_ECHO:
                        case T_PRINT:
                            $this->printsOutput = true;
                            break;
                        case T_NAMESPACE:
                            $currentMode = $modes['namespaceParsing'];
                            break;
                        case T_FUNCTION:
                            $currentMode = $modes['functionParsing'];
                            break;
                        case T_CLASS:
                            if ($previousToken()->getType() !== T_DOUBLE_COLON) {
                                $currentMode = $modes['classParsing'];
                            }
                            break;
                        case T_USE:
                            $currentMode = $modes['importParsing'];
                            break;
                    }
                } elseif ($currentMode & $modes['namespaceParsing']) {
                    if (is_null($this->namespace)) {
                        $this->namespace = '';
                    }
                    switch ($token->getType()) {
                        case T_STRING:
                        case T_NS_SEPARATOR:
                            $this->namespace .= $token->getContent();
                            break;
                        case T_UNKNOWN:
                            $currentMode = $modes['none'];
                            break;
                    }
                } elseif ($currentMode & $modes['classParsing']) {
                    if (is_null($this->class)) {
                        $this->class = '';
                    }
                    if ($currentMode & $modes['extendsImplements']) {
                        if ($token->getType() !== T_UNKNOWN && $token->getContent() !== '{') {
                            continue;
                        }
                    }
                    switch ($token->getType()) {
                        case T_STRING:
                            $this->class .= $token->getContent();
                            break;
                        case T_UNKNOWN:
                            if ($token->getContent() === '{') {
                                $braces['opening'] = 1;
                                $braces['closing'] = 0;
                                $currentMode = $modes['insideClass'];
                            }
                            break;
                        case T_IMPLEMENTS:
                        case T_EXTENDS:
                            $currentMode = $modes['classParsing'] | $modes['extendsImplements'];
                            break;
                    }
                } elseif ($currentMode & $modes['insideClass']) {
                    if ($braces['opening'] === $braces['closing']) {
                        $braces['opening'] = 0;
                        $braces['closing'] = 0;
                        $currentMode = $modes['none'];
                    } else {
                        if ($token->getType() === T_UNKNOWN) {
                            switch ($token->getContent()) {
                                case '{':
                                    $braces['opening']++;
                                    break;
                                case '}':
                                    $braces['closing']++;
                            }
                        }
                    }
                } elseif ($currentMode & $modes['functionParsing']) {
                    switch ($token->getType()) {
                        case T_STRING:
                            $functionName .= $token->getContent();
                            break;
                        case T_UNKNOWN:
                            if ($token->getContent() === '{') {
                                $braces['opening'] = 1;
                                $braces['closing'] = 0;
                                $currentMode = $modes['insideFunction'];
                                $this->functions[] = $functionName;
                                $functionName = '';
                            }
                            break;
                    }
                } elseif ($currentMode & $modes['insideFunction']) {
                    if ($braces['opening'] === $braces['closing']) {
                        $currentMode = $modes['none'];
                    } else {
                        if ($token->getType() === T_UNKNOWN) {
                            switch ($token->getContent()) {
                                case '{':
                                    $braces['opening']++;
                                    break;
                                case '}':
                                    $braces['closing']++;
                            }
                        }
                    }
                } elseif ($currentMode & $modes['importParsing']) {
                    if ($token->getType() === T_UNKNOWN) {
                        $currentMode = $modes['none'];
                    }
                }
            }

            if (!is_null($this->cache)) {
                $cachedData = new CachedData();
                $cachedData->setClass($this->class);
                $cachedData->setContainsInlineHtml($this->inlineHtml);
                $cachedData->setContainsPhpCode($this->containsPhpCode);
                $cachedData->setFunctions($this->functions);
                $cachedData->setNamespace($this->namespace);
                $cachedData->setPrintsOutput($this->printsOutput);

                $this->cache->store($cachedData);
            }

            $this->parsed = true;
        }
    }
}
