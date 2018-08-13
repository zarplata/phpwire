<?php
namespace Zp\PHPWire\Danilson\Key;

use Zp\PHPWire\Danilson\Interfaces\Container\Key\SpecificationInterface;

class AsStringAlias implements SpecificationInterface
{
    public function isSatisfiedBy($value): bool
    {
        return is_string($value) && !(new AsClass())->isSatisfiedBy($value);
    }
}