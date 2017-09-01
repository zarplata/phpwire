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
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getService($id)
    {
        return self::$container->get($id);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        self::setStaticContainer($container);
    }

    /**
     * @param ContainerInterface $container
     */
    public static function setStaticContainer(ContainerInterface $container): void
    {
        self::$container = $container;
    }
}
