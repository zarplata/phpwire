<?php

namespace Zp\PHPWire\Tests\Fixtures;

class MagicMethod
{
    private $arguments = [];

    /**
     * @param string $name
     * @param array $arguments
     * @return MagicMethod|mixed
     */
    public function __call($name, $arguments)
    {
        $this->arguments[$name] = $arguments;
    }

    public function get()
    {
        return $this->arguments;
    }
}
