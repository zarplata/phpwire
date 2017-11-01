<?php

namespace Zp\PHPWire\Tests\Fixtures;

class StringDependency
{
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
