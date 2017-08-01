<?php

namespace Zp\Container;

use ProxyManager\Proxy\LazyLoadingInterface;
use Psr\Container\ContainerInterface;

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

    public function __construct(array $definitions, ProxyFactory $proxyFactory = null)
    {
        $this->singletons = [
            'container' => $this,
            static::class => $this,
        ];
        $this->definitions = $definitions;
        $this->proxyFactory = $proxyFactory;
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
            throw new ContainerException(
                sprintf('Unable to create instance of entry `%s`: %s', $id, $e->getMessage()), 0, $e
            );
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
     * @param string $id
     * @return Definition|mixed
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
        if ($this->proxyFactory !== null && $definition->isLazy) {
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
        $proxy = $this->proxyFactory->createProxy(
            $definition->className,
            function (& $wrappedObject, $proxy, $method, $params, & $initializer) use ($definition) {
                $wrappedObject = $this->createInstance($definition);
                $initializer = null; // turning off further lazy initialization
                return true;
            }
        );
        return $proxy;
    }

    /**
     * Создает экземпляр объекта
     *
     * @param Definition $definition
     * @return mixed
     * @throws ContainerException
     * @throws \ReflectionException
     */
    private function createInstance(Definition $definition)
    {
        if ($definition->isFactory) {
            $factory = $definition->factory;
            return $factory($this);
        }

        $this->ensureDefinitionClassNameIsNotEmpty($definition);

        $reflector = new \ReflectionClass($definition->className);
        $constructor = $reflector->getConstructor();

        $instance = new $definition->className(
            ...$this->resolveArguments($definition->arguments, $constructor)
        );

        foreach ($definition->methods as $methodName => $methodArgs) {
            $callable = [$instance, $methodName];
            if (!is_callable($callable)) {
                throw new ContainerException(sprintf(
                    'Definition `%s` does not exists method `%s::%s`',
                    $definition->name,
                    get_class($instance),
                    $methodName
                ));
            }

            $method = $reflector->getMethod($methodName);
            $callable(...$this->resolveArguments($methodArgs, $method));
        }

        return $instance;
    }

    /**
     * @param array $arguments
     * @param \ReflectionMethod $method
     * @return array
     * @throws ContainerException
     */
    private function resolveArguments(array $arguments, \ReflectionMethod $method)
    {
        if ($method->getNumberOfParameters() === 0) {
            return [];
        }

        $parameters = $method->getParameters();

        $result = [];
        foreach ($parameters as $parameter) {
            // match by position
            if (array_key_exists($parameter->getPosition(), $arguments)) {
                $result[] = $this->resolveArgument($arguments[$parameter->getPosition()]);
                continue;
            }
            // match by name
            if (array_key_exists($parameter->getName(), $arguments)) {
                $result[] = $this->resolveArgument($arguments[$parameter->getName()]);
                continue;
            }
            // autowiring
            $class = $parameter->getClass();
            $className = $class ? $class->getName() : null;
            if ($class !== null && $this->has($className)) {
                $result[] = $this->get($className);
                continue;
            }
            // skip optional parameters
            if ($parameter->isOptional()) {
                $result[] = $parameter->getDefaultValue();
                continue;
            }

            throw new ContainerException(
                "Unable to resolve parameter `{$parameter->name}` of method `{$method->name}`"
            );
        }
        return $result;
    }

    /**
     * @param string $value
     * @return mixed
     * @throws ContainerException
     */
    private function resolveArgument($value)
    {
        if (is_string($value)) {
            if ($value[0] === '$') {
                return $this->get(substr($value, 1));
            }
            if ($this->has($value)) {
                return $this->get($value);
            }
        }
        if ($value instanceof \Closure) {
            return $value($this);
        }
        return $value;
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
