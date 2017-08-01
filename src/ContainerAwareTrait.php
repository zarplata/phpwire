<?php

namespace Zp\Container;

use Psr\Container\ContainerInterface;

trait ContainerAwareTrait
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param string $id
     * @return mixed
     * @throws Container
     */
    public function getService($id)
    {
        return $this->container->get($id);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
