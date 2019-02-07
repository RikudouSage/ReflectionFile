<?php

namespace Rikudou\Tests\Data;

class ClassThatExtendsAndImplements extends NamespacedClass implements \Countable
{
    public function count()
    {
        return 0;
    }
}
