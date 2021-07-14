<?php

namespace Zp\PHPWire\Arguments;

use Psr\Container\ContainerInterface;
use SuperClosure\Serializer;
use Zp\PHPWire\ContainerCompiler;

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
        $s = ContainerCompiler::serializeClosure($this->closure);
        return sprintf('call_user_func(%s, $container)', $s);
    }
}
