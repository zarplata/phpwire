<?php

namespace Zp\Container;

use Psr\Container\ContainerInterface;

trait ContainerAwareStaticTrait
{
    use ContainerAwareTrait;

    /**
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * @param string $id
     * @return mixed
     */
    public function getService($id)
    {
        return self::$container->get($id);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return self::$container;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        self::setStaticContainer($container);
    }

    /**
     * @param ContainerInterface $container
     */
    public static function setStaticContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }
}
