<?php
namespace Zp\PHPWire\Danilson\Key;

use Zp\PHPWire\Danilson\Interfaces\Container\Key\SpecificationInterface;

class AsClass implements SpecificationInterface
{
    public function isSatisfiedBy($value): bool
    {
        return class_exists($value);
    }
}