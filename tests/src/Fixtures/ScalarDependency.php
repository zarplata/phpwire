<?php

namespace Zp\PHPWire\Tests\Fixtures;

class ScalarDependency
{
    /**
     * @var int
     */
    private $number;

    /**
     * @param $number
     */
    public function __construct($number)
    {
        $this->number = $number;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }
}
