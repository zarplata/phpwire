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
        foreach ($arguments as $name => $argument) {
            try {
                $result[] = $argument->resolve($container);
            } catch (\Throwable $e) {
                $nameOfArgument = is_numeric($name) ? sprintf('#%s', $name) : $name;

                throw new ContainerException(
                    sprintf("Unable to resolve value of argument %s", $nameOfArgument),
                    0,
                    $e
                );
            }
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
    ): array {
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
            $class = $parameter->getClass();

            switch (true) {
                // match by position
                case array_key_exists($parameter->getPosition(), $definitions):
                    $result[] = $class == null && $parameter->getType() !== null
                        ? new ValueArgument($definitions[$parameter->getPosition()])
                        : static::parseDefinition($container, $definitions[$parameter->getPosition()]);
                    break;
                // match by name
                case array_key_exists($parameter->getName(), $definitions):
                    $result[] = $class == null && $parameter->getType() !== null
                        ? new ValueArgument($definitions[$parameter->getName()])
                        : static::parseDefinition($container, $definitions[$parameter->getName()]);
                    break;
                // autowiring
                case $class !== null && $container->has($class->getName()):
                    $result[] = new ContainerArgument($class->getName());
                    break;
                // skip optional parameters
                case $parameter->isOptional():
                    $result[] = new ValueArgument($parameter->getDefaultValue());
                    break;
                // hopelessness...
                default:
                    throw new ContainerException("Please provide definition for argument `{$parameter->name}`");
            }
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
