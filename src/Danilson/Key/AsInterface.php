<?php
namespace Zp\PHPWire\Danilson\Key;

use Zp\PHPWire\Danilson\Interfaces\Container\Key\SpecificationInterface;

class AsInterface implements SpecificationInterface
{
    public function isSatisfiedBy($value): bool
    {
        return interface_exists($value);
    }
}