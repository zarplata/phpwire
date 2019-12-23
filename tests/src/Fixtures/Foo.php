<?php

namespace Zp\PHPWire\Tests\Fixtures;

class Foo implements FooInterface
{
    public function test(): string
    {
        return 'its foo class';
    }
}
