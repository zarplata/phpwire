<?php
namespace Zp\PHPWire\Danilson\Definition;

use Zp\PHPWire\Danilson\Interfaces\Container\Definition\SpecificationInterface;

class AsIs implements SpecificationInterface
{
    public function isSatisfiedBy($value): bool
    {
        return true;
    }
}