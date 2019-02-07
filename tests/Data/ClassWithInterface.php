<?php

namespace Rikudou\Tests\Data;

class ClassWithInterface implements \Countable
{
    public function count()
    {
        return 0;
    }
}
