<?php

namespace Zp\PHPWire\Arguments;

use Psr\Container\ContainerInterface;
use SuperClosure\Serializer;

class ClosureArgument implements ArgumentInterface
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @param \Closure $closure
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function resolve(ContainerInterface $container)
    {
        return call_user_func($this->closure, $container);
    }

    /**
     * @return string
     */
    public function resolveSourceCode(): string
    {
        $serializer = new Serializer();
        /** @var array $data */
        $data = $serializer->getData($this->closure);
        return sprintf('call_user_func(%s, $container)', rtrim($data['code'], ';'));
    }
}
