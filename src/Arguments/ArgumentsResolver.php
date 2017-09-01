<?php

namespace Zp\Container\Arguments;

use Psr\Container\ContainerInterface;
use Zp\Container\ContainerException;

class ArgumentsResolver
{
    /**
     * Resolve arguments definitions as is.
     *
     * @param ContainerInterface $container
     * @param array $arguments
     * @param \ReflectionMethod|null $method
     * @return array
     * @throws \Zp\Container\ContainerException
     */
    public static function resolveAsIs(
        ContainerInterface $container,
        array $arguments,
        \ReflectionMethod $method = null
    ): array
    {
        $result = [];
        foreach (static::resolve($container, $arguments, $method) as $definition) {
            $result[] = $definition->resolve($container);
        }
        return $result;
    }

    /**
     * Resolve argument definitions.
     *
     * @param ContainerInterface $container
     * @param array $arguments
     * @param \ReflectionMethod $method
     * @return ArgumentInterface[]
     * @throws \Zp\Container\ContainerException
     */
    public static function resolve(
        ContainerInterface $container,
        array $arguments,
        \ReflectionMethod $method = null
    ): array
    {
        $numberOfArguments = $method ? $method->getNumberOfParameters() : 0;
        if (count($arguments) > $numberOfArguments) {
            $numberOfRedundantly = $numberOfArguments - count($arguments);
            throw new ContainerException("Method definition have {$numberOfRedundantly} redundantly arguments");
        }

        if ($method === null || $method->getNumberOfParameters() === 0) {
            return [];
        }

        $parameters = $method->getParameters();

        $result = [];
        foreach ($parameters as $parameter) {
            // match by position
            if (array_key_exists($parameter->getPosition(), $arguments)) {
                $result[] = static::resolveValue($container, $arguments[$parameter->getPosition()]);
                continue;
            }
            // match by name
            if (array_key_exists($parameter->getName(), $arguments)) {
                $result[] = static::resolveValue($container, $arguments[$parameter->getName()]);
                continue;
            }
            // autowiring
            $class = $parameter->getClass();
            $className = $class ? $class->getName() : null;
            if ($class !== null && $container->has($className)) {
                $result[] = new ContainerArgument($className);
                continue;
            }
            // skip optional parameters
            if ($parameter->isOptional()) {
                $result[] = new ValueArgument($parameter->getDefaultValue());
                continue;
            }
            // hopelessness...
            throw new ContainerException(
                "Please provide definition for argument `{$parameter->name}`"
            );
        }
        return $result;
    }

    /**
     * @param ContainerInterface $container
     * @param string $value
     * @return mixed
     */
    private static function resolveValue(ContainerInterface $container, $value)
    {
        if (is_string($value)) {
            if ($value[0] === '$') {
                return new ContainerArgument(substr($value, 1));
            }
            if ($container->has($value)) {
                return new ContainerArgument($value);
            }
        }
        if ($value instanceof \Closure) {
            return new ClosureArgument($value);
        }
        return new ValueArgument($value);
    }
}
