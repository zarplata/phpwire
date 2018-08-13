<?php
namespace Zp\PHPWire\Danilson\Definition\Dependency;

use Zp\PHPWire\Danilson\Interfaces\Container\Definition\Dependency\SpecificationInterface;

class WithNotImplements implements SpecificationInterface
{
    public function isSatisfiedBy($value): bool
    {
        // @todo
        return true;
    }
}