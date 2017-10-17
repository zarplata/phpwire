<?php

namespace Zp\PHPWire\Arguments;

use Psr\Container\ContainerInterface;

class ValueArgument implements ArgumentInterface
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function resolve(ContainerInterface $container)
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function resolveSourceCode(): string
    {
        return var_export($this->value, true);
    }
}
