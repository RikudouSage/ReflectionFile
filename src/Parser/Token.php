<?php

namespace Rikudou\Parser;

class Token
{
    /**
     * @var int
     */
    private $type = T_UNKNOWN;

    private $typeString;

    /**
     * @var string
     */
    private $content;

    public function __construct($token)
    {
        if (is_array($token)) {
            $this->type = $token[0];
            $this->content = $token[1];
        } else {
            $this->content = $token;
        }
        $this->typeString = token_name($this->type);
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
