<?php

namespace Zp\Container\Arguments;

use Psr\Container\ContainerInterface;

class ContainerArgument implements ArgumentInterface
{
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param ContainerInterface $container
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function resolve(ContainerInterface $container)
    {
        return $container->get($this->name);
    }

    /**
     * @return string
     */
    public function resolveSourceCode()
    {
        return sprintf('$container->get(\'%s\')', $this->name);
    }
}
