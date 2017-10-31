<?php

namespace Zp\PHPWire\Tests\Fixtures;

class StrictDependency extends ClassDependency
{
    public function __construct(Foo $foo)
    {
        parent::__construct($foo);
    }
}
