<?php
namespace Zp\PHPWire\Danilson\Value\ClassName;

use Zp\PHPWire\Danilson\Interfaces\Container\Value\SpecificationInterface;

class AsIs implements SpecificationInterface
{
    public function isSatisfiedBy($value): bool
    {
        return isset($value['class']) && class_exists($value['class']);
    }
}