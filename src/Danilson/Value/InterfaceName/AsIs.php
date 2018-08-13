<?php
namespace Zp\PHPWire\Danilson\Value\InterfaceName;

use Zp\PHPWire\Danilson\Interfaces\Container\Value\SpecificationInterface;

class AsIs implements SpecificationInterface
{
    public function isSatisfiedBy($value): bool
    {
        return isset($value['class']) && interface_exists($value['class']);
    }
}