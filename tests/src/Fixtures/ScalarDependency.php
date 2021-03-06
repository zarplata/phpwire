<?php

namespace Zp\PHPWire\Tests\Fixtures;

class ScalarDependency
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}
