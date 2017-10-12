<?php

namespace Zp\PHPWire\Arguments;

use Psr\Container\ContainerInterface;

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
     * @throws \ReflectionException
     */
    public function resolveSourceCode(): string
    {
        $reflector = new \ReflectionFunction($this->closure);
        $content = file($reflector->getFileName());
        $startLine = $reflector->getStartLine();
        $endLine = $reflector->getEndLine();
        $code = trim(implode('', array_slice($content, $startLine, $endLine - $startLine - 1)));
        return sprintf('call_user_func(function(ContainerInterface $container) {%s}, $container)', $code);
    }
}
