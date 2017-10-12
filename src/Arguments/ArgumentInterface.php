<?php

namespace Zp\PHPWire\Arguments;

use Psr\Container\ContainerInterface;

interface ArgumentInterface
{
    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function resolve(ContainerInterface $container);

    /**
     * @return string
     */
    public function resolveSourceCode(): string;
}
