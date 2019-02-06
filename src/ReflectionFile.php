<?php

namespace Rikudou;

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
     * @param string $file
     *
     * @throws ReflectionException
     */
    public function __construct(string $file)
    {
        if (!file_exists($file)) {
            throw new ReflectionException("The file '{$file}' does not exist");
        }
        $this->file = $file;
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

    private function parse()
    {
        if (!$this->parsed) {
            $content = file_get_contents($this->file);
            assert(is_string($content));

            $modes = [
                'none' => 0,
                'classParsing' => 1,
                'namespaceParsing' => 2,
            ];

            $currentMode = $modes['none'];

            $tokens = token_get_all($content);
            foreach ($tokens as $token) {
                $token = new Token($token);

                if ($currentMode === $modes['none']) {
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
                        case T_CLASS:
                            $currentMode = $modes['classParsing'];
                            break;
                    }
                } elseif ($currentMode === $modes['namespaceParsing']) {
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
                } elseif ($currentMode === $modes['classParsing']) {
                    if (is_null($this->class)) {
                        $this->class = '';
                    }
                    switch ($token->getType()) {
                        case T_STRING:
                            $this->class .= $token->getContent();
                            break;
                        case T_UNKNOWN:
                            $currentMode = $modes['none'];
                            break;
                    }
                }
            }
            $this->parsed = true;
        }
    }
}
