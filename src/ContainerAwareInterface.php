<?php

namespace Zp\PHPWire;

use Psr\Container\ContainerInterface;

/**
 * Should be implemented by classes that depends on a Container.
 */
interface ContainerAwareInterface
{
    /**
     * Sets the container.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container);
}
