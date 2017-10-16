<?php

namespace Zp\PHPWire\Arguments;

use Psr\Container\ContainerInterface;
use Zp\PHPWire\ContainerException;

class ArgumentsResolver
{
    /**
     * Resolve values of arguments.
     *
     * @param ContainerInterface $container
     * @param ArgumentInterface[] $arguments
     * @return array
     */
    public static function resolveArgumentsToValues(ContainerInterface $container, array $arguments): array
    {
        $result = [];
        foreach ($arguments as $argument) {
            $result[] = $argument->resolve($container);
        }
        return $result;
    }

    /**
     * Parse definitions as is to ArgumentInterface.
     *
     * @param ContainerInterface $container
     * @param array $definitions
     * @return ArgumentInterface[]
     */
    public static function parseDefinitionsAsIs(ContainerInterface $container, array $definitions): array
    {
        $result = [];
        foreach ($definitions as $definition) {
            $result[] = self::parseDefinition($container, $definition);
        }
        return $result;
    }

    /**
     * Parse definitions using reflection signature to ArgumentInterface.
     *
     * @param ContainerInterface $container
     * @param array $definitions
     * @param \ReflectionMethod $method
     * @return ArgumentInterface[]
     * @throws ContainerException
     */
    public static function parseDefinitionsByMethodSignature(
        ContainerInterface $container,
        array $definitions,
        \ReflectionMethod $method = null
    ): array
    {
        $numberOfArguments = $method ? $method->getNumberOfParameters() : 0;
        if (count($definitions) > $numberOfArguments) {
            $numberOfRedundantly = count($definitions) - $numberOfArguments;
            throw new ContainerException("Method have {$numberOfRedundantly} redundantly defined arguments");
        }

        if ($method === null || $method->getNumberOfParameters() === 0) {
            return [];
        }

        $parameters = $method->getParameters();

        $result = [];
        foreach ($parameters as $parameter) {
            // match by position
            if (array_key_exists($parameter->getPosition(), $definitions)) {
                $result[] = static::parseDefinition($container, $definitions[$parameter->getPosition()]);
                continue;
            }
            // match by name
            if (array_key_exists($parameter->getName(), $definitions)) {
                $result[] = static::parseDefinition($container, $definitions[$parameter->getName()]);
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
     * Parse definition to ArgumentInterface.
     *
     * @param ContainerInterface $container
     * @param string $definition
     * @return ArgumentInterface
     */
    private static function parseDefinition(ContainerInterface $container, $definition): ArgumentInterface
    {
        if (is_string($definition)) {
            if ($definition[0] === '$') {
                return new ContainerArgument(substr($definition, 1));
            }
            if ($container->has($definition)) {
                return new ContainerArgument($definition);
            }
        }
        if ($definition instanceof \Closure) {
            return new ClosureArgument($definition);
        }
        return new ValueArgument($definition);
    }
}
