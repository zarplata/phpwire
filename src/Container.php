<?php

namespace Zp\Container;

use ProxyManager\Proxy\LazyLoadingInterface;
use Psr\Container\ContainerInterface;
use Zp\Container\Arguments\ArgumentsResolver;

/**
 * IoC container.
 */
class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $singletons;

    /**
     * @var array
     */
    private $definitions;

    /**
     * @var ProxyFactory
     */
    private $proxyFactory;

    /**
     * @var string
     */
    private $compiledContainerFile;

    /**
     * @var \Zp\Container\CompiledContainer
     */
    private $compiledContainer;

    public function __construct(array $definitions, ProxyFactory $proxyFactory = null, $compiledContainerFile = null)
    {
        $this->singletons = [
            'container' => $this,
            static::class => $this,
        ];
        $this->definitions = $definitions;
        $this->proxyFactory = $proxyFactory;
        $this->compiledContainerFile = $compiledContainerFile;

        if ($compiledContainerFile && file_exists($compiledContainerFile)) {
            $this->loadCompiledContainer();
        }
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id
     * @return bool
     * @throws ContainerException
     */
    public function has($id)
    {
        $this->ensureIdentifierIsString($id);
        $this->ensureIdentifierIsNotEmpty($id);

        return isset($this->definitions[$id]) || array_key_exists($id, $this->singletons);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id
     * @return mixed
     * @throws ContainerException
     */
    public function get($id)
    {
        $this->ensureIdentifierIsString($id);
        $this->ensureIdentifierIsNotEmpty($id);

        if (array_key_exists($id, $this->singletons)) {
            return $this->singletons[$id];
        }

        $this->ensureDefinitionIsExists($id);

        try {
            return $this->resolve($this->getDefinition($id));
        } catch (\Exception $e) {
            $message = sprintf('Unable to create instance of entry `%s`: %s', $id, $e->getMessage());
            throw new ContainerException($message, 0, $e);
        }
    }

    /**
     * Set an instance of entry to the container by given identifier.
     *
     * @param string $id
     * @param mixed $value
     */
    public function set($id, $value)
    {
        if ($value instanceof \Closure) {
            $this->definitions[$id] = $value;
            unset($this->singletons[$id]);
        } else {
            $this->singletons[$id] = $value;
        }
    }

    /**
     * Drop state of container.
     * Useful for unit tests.
     */
    public function reset()
    {
        $this->singletons = [
            'container' => $this,
            static::class => $this,
        ];
        $this->definitions = [];
    }

    /**
     * Generation of proxy classes. Useful for prepare on build.
     *
     * @throws ContainerException
     */
    public function generateProxies()
    {
        foreach (array_keys($this->definitions) as $id) {
            $definition = $this->getDefinition($id);
            if ($definition->isLazy) {
                $this->proxyFactory->generateProxy($definition->className);
            }
        }
    }

    /**
     * Generate the  for definitions.
     *
     * @throws \Zp\Container\ContainerException
     * @throws \Zend\Code\Generator\Exception\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function compileContainer()
    {
        $builder = new ContainerCompiler();
        foreach (array_keys($this->definitions) as $id) {
            $builder->addDefinition($this->getDefinition($id), $this);
        }
        $builder->compileAndSave($this->compiledContainerFile);
    }

    /**
     * Load compiled container.
     *
     * @return void
     * @throws \Zp\Container\ContainerException
     */
    public function loadCompiledContainer()
    {
        if ($this->compiledContainer) {
            throw new ContainerException('Compiled container already loaded');
        }
        /** @noinspection PhpIncludeInspection */
        require_once $this->compiledContainerFile;
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        /** @noinspection PhpUndefinedClassInspection */
        $this->compiledContainer = new \Zp\Container\CompiledContainer;
    }

    /**
     * Retrieve definition.
     *
     * @param string $id
     * @return Definition
     * @throws ContainerException
     */
    private function getDefinition($id)
    {
        $definition = $this->definitions[$id];
        if ($definition instanceof Definition) {
            return $definition;
        }
        return $this->definitions[$id] = new Definition($id, $definition);
    }

    /**
     * Resolve definition.
     *
     * @param Definition $definition
     * @return mixed|LazyLoadingInterface
     * @throws ContainerException
     * @throws \ReflectionException
     */
    private function resolve(Definition $definition)
    {
        if ($definition->isLazy && $this->proxyFactory !== null) {
            $instance = $this->createProxy($definition);
        } else {
            $instance = $this->createInstance($definition);
        }
        if ($definition->isSingleton) {
            $this->singletons[$definition->name] = $instance;
        }
        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($this);
        }
        return $instance;
    }

    /**
     * Create instance of definition wrapped by lazy-proxy.
     *
     * @param Definition $definition
     * @return LazyLoadingInterface Proxy instance
     * @throws ContainerException
     * @throws \ReflectionException
     */
    private function createProxy(Definition $definition)
    {
        /** @noinspection PhpUnusedParameterInspection */
        /** @noinspection MoreThanThreeArgumentsInspection */
        return $this->proxyFactory->createProxy(
            $definition->className,
            function (& $wrappedObject, $proxy, $method, $params, & $initializer) use ($definition) {
                $wrappedObject = $this->createInstance($definition);
                $initializer = null; // turning off further lazy initialization
                return true;
            }
        );
    }

    /**
     * Create instance of object.
     *
     * @param Definition $definition
     * @return mixed
     * @throws ContainerException
     * @throws \ReflectionException
     */
    private function createInstance(Definition $definition)
    {
        if ($this->compiledContainer) {
            $compiledMethod = $definition->compiledMethod;
            return $this->compiledContainer->$compiledMethod($this);
        }

        if ($definition->isFactory) {
            $factory = $definition->factory;
            return $factory($this);
        }

        $this->ensureDefinitionClassNameIsNotEmpty($definition);

        $reflector = new \ReflectionClass($definition->className);
        $constructor = $reflector->getConstructor();

        try {
            $instance = new $definition->className(
                ...ArgumentsResolver::resolveAsIs($this, $definition->arguments, $constructor)
            );
        } catch (\Exception $e) {
            throw new ContainerException("Unable to invoke arguments to constructor: {$e->getMessage()}", 0, $e);
        }

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
                $method->invoke($instance, ...ArgumentsResolver::resolveAsIs($this, $methodArgs, $method));
            } catch (\Exception $e) {
                throw new ContainerException(
                    sprintf('Unable to invoke arguments to method %s: %s', $methodName, $e->getMessage()),
                    0,
                    $e
                );
            }
        }

        return $instance;
    }

    /**
     * @param string $id
     * @throws ContainerException
     */
    private function ensureIdentifierIsString($id)
    {
        if (!is_string($id)) {
            throw new ContainerException('Identifier of entry should be string');
        }
    }

    /**
     * @param string $id
     * @throws ContainerException
     */
    private function ensureIdentifierIsNotEmpty($id)
    {
        if ($id === '') {
            throw new ContainerException('Identifier of entry should not be empty string');
        }
    }

    /**
     * @param string $id
     * @throws NotFoundException
     */
    private function ensureDefinitionIsExists($id)
    {
        if (!isset($this->definitions[$id])) {
            throw new NotFoundException("Requested a non-existent container entry `{$id}`");
        }
    }

    /**
     * @param Definition $definition
     * @throws ContainerException
     */
    private function ensureDefinitionClassNameIsNotEmpty(Definition $definition)
    {
        if (!$definition->className) {
            throw new ContainerException("Definition of entry `{$definition->name}` has empty class name");
        }
    }
}
