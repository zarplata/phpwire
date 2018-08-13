<?php
namespace Zp\PHPWire\Danilson\Value;

use Zp\PHPWire\Danilson\Interfaces\Container\Value\SpecificationInterface;

class AsEmpty implements SpecificationInterface
{
    public function isSatisfiedBy($value): bool
    {
        return empty($value);
    }
}