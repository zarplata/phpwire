<?php

namespace Zp\Container;

use Psr\Container\ContainerInterface;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zp\Container\Arguments\ArgumentInterface;
use Zp\Container\Arguments\ArgumentsResolver;

class ContainerCompiler
{
    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    public function __construct()
    {
        $this->classGenerator = (new ClassGenerator)
            ->setName('Zp\\Container\\CompiledContainer')
            ->addTrait('\\' . ContainerAwareTrait::class);
    }

    /**
     * Add definition
     *
     * @param Definition $definition
     * @param ContainerInterface $container
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \ReflectionException
     * @throws \Zp\Container\ContainerException
     */
    public function addDefinition(Definition $definition, ContainerInterface $container): void
    {
        try {
            $sourceCode = $definition->isFactory
                ? $this->closureToSourceCode($definition->factory)
                : $this->classToSourceCode($definition, $container);
        } catch (\Exception $e) {
            throw new ContainerException("Unable to compile definition `{$definition->name}`: {$e->getMessage()}");
        }

        $this->classGenerator->addMethod(
            $definition->compiledMethod,
            ['container'],
            MethodGenerator::FLAG_PUBLIC,
            $sourceCode
        );
    }

    /**
     * Compile definitions
     * @return string
     */
    public function compile(): string
    {
        return '<?php' . PHP_EOL . $this->classGenerator->generate();
    }

    /**
     * Compile definitions and save to file
     * @param string $filename
     */
    public function compileAndSave($filename): void
    {
        file_put_contents($filename, $this->compile());
    }

    /**
     * @param ArgumentInterface[] $arguments
     * @return string
     */
    private function argumentsToSourceCode(array $arguments): string
    {
        $sourceCodeLines = [];
        foreach ($arguments as $argument) {
            $sourceCodeLines[] = $argument->resolveSourceCode();
        }
        return implode(', ', $sourceCodeLines);
    }

    /**
     * @param \Closure $c
     * @return string
     * @throws \ReflectionException
     */
    private function closureToSourceCode(\Closure $c): string
    {
        $reflector = new \ReflectionFunction($c);
        $lines = file($reflector->getFileName());
        $sourceCode = array_slice(
            $lines,
            $reflector->getStartLine(),
            $reflector->getEndLine() - $reflector->getStartLine() - 1
        );
        return implode(PHP_EOL, array_map('trim', $sourceCode));
    }

    /**
     * @param Definition $definition
     * @param ContainerInterface $container
     * @return string
     * @throws \Zp\Container\ContainerException
     * @throws \ReflectionException
     */
    private function classToSourceCode(Definition $definition, ContainerInterface $container): string
    {
        $reflector = new \ReflectionClass($definition->className);
        // constructor
        try {
            $ctorArgumentsSourceCode = $this->argumentsToSourceCode(
                ArgumentsResolver::resolve($container, $definition->arguments, $reflector->getConstructor())
            );
        } catch (\Exception $e) {
            throw new ContainerException("Unable to compile arguments for constructor: {$e->getMessage()}", 0, $e);
        }

        $sourceCodeLines = [
            sprintf('$instance = new \%s(%s);', $definition->className, $ctorArgumentsSourceCode)
        ];
        // methods
        foreach ($definition->methods as $methodName => $methodArgs) {
            if (!$reflector->hasMethod($methodName)) {
                throw new ContainerException(sprintf(
                    'Definition `%s` have non-existent method `%s::%s`',
                    $definition->name,
                    $definition->className,
                    $methodName
                ));
            }
            $method = $reflector->getMethod($methodName);
            if ($method->isPrivate()) {
                throw new ContainerException(sprintf(
                    'Definition `%s` have private method `%s::%s`',
                    $definition->name,
                    $definition->className,
                    $methodName
                ));
            }
            try {
                $methodArgumentsSourceCode = $this->argumentsToSourceCode(
                    ArgumentsResolver::resolve($container, $methodArgs, $method)
                );
            } catch (\Exception $e) {
                throw new ContainerException(
                    sprintf('Unable to compile arguments for method %s: %s', $methodName, $e->getMessage()),
                    0,
                    $e
                );
            }
            $sourceCodeLines[] = sprintf('$instance->%s(%s)', $methodName, $methodArgumentsSourceCode);
        }
        $sourceCodeLines[] = sprintf('return $instance;');

        return implode(PHP_EOL, $sourceCodeLines);
    }
}
