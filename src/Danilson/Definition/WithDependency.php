<?php
namespace Zp\PHPWire\Danilson\Definition;

use Zp\PHPWire\Danilson\Interfaces\Container\Definition\SpecificationInterface;

class WithDependency implements SpecificationInterface
{
    public function isSatisfiedBy($value): bool
    {
        // @todo
        return true;
    }
}
