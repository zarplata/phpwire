<?php
namespace Zp\PHPWire\Danilson\Interfaces;


interface SpecificationInterface
{
    public function isSatisfiedBy($value): bool;
}