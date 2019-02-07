<?php

namespace Rikudou\Tests\Data;

class ClassWithEchoStatement
{
    public function hello()
    {
        echo 'there';
        $this->saySomething();
    }

    private function saySomething()
    {
        echo 'hello';
    }
}
