<?php

namespace Zp\PHPWire;

use Psr\Container\ContainerInterface;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\Exception\InvalidArgumentException;
use Zend\Code\Generator\MethodGenerator;
use Zp\PHPWire\Arguments\ArgumentInterface;
use Zp\PHPWire\Arguments\ArgumentsResolver;

class ContainerCompiler
{
    /**
     * @var ClassGenerator
     */
    private $classGenerator;

    public function __construct()
    {
        $this->classGenerator = (new ClassGenerator)
            ->setName('Zp\\PHPWire\\CompiledContainer')
            ->addTrait('\\' . ContainerAwareTrait::class);
    }

    /**
     * Add definition
     *
     * @param Definition $definition
     * @param ContainerInterface $container
     * @throws InvalidArgumentException
     * @throws ContainerException
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
    public function compileAndSave(string $filename): void
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
     * @throws ContainerException
     */
    private function classToSourceCode(Definition $definition, ContainerInterface $container): string
    {
        $reflector = new \ReflectionClass($definition->className);
        // constructor
        try {
            $ctorArgumentsSourceCode = $this->argumentsToSourceCode(
                ArgumentsResolver::parseDefinitionsByMethodSignature(
                    $container,
                    $definition->arguments,
                    $reflector->getConstructor()
                )
            );
        } catch (\Exception $e) {
            throw new ContainerException("Unable to compile arguments for constructor: {$e->getMessage()}", 0, $e);
        }
        // instantiator
        $sourceCodeLines = [
            sprintf('$instance = new \%s(%s);', $definition->className, $ctorArgumentsSourceCode)
        ];
        // methods
        foreach ($definition->methods as $methodName => $methodDefinitions) {
            try {
                switch (true) {
                    // check own method
                    case $reflector->hasMethod($methodName) === true:
                        $method = $reflector->getMethod($methodName);
                        if ($method->isPrivate()) {
                            throw new ContainerException(sprintf('method `%s` is private', $methodName));
                        }
                        $arguments = ArgumentsResolver::parseDefinitionsByMethodSignature(
                            $container,
                            $methodDefinitions,
                            $method
                        );
                        break;

                    // check magic method
                    case method_exists($reflector->getName(), '__call') === true:
                        $arguments = ArgumentsResolver::parseDefinitionsAsIs($container, $methodDefinitions);
                        break;

                    // oh well
                    default:
                        throw new ContainerException(sprintf(
                            'non-existent method `%s::%s`',
                            $reflector->getName(),
                            $methodName
                        ));

                }
                $sourceCodeLines[] = sprintf(
                    '$instance->%s(%s)',
                    $methodName,
                    $this->argumentsToSourceCode($arguments)
                );
            } catch (\Exception $e) {
                throw new ContainerException(
                    sprintf('Unable to compile arguments for method %s: %s', $methodName, $e->getMessage()),
                    0,
                    $e
                );
            }
        }
        $sourceCodeLines[] = sprintf('return $instance;');

        return implode(PHP_EOL, $sourceCodeLines);
    }
}
