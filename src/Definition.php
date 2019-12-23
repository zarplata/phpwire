<?php

namespace Zp\PHPWire;

class Definition
{
    const CONFIG_NAME = 'name';
    const CONFIG_CLASS = 'class';
    const CONFIG_ARGS = 'args';
    const CONFIG_METHODS = 'methods';
    const CONFIG_FACTORY = 'factory';
    const CONFIG_SINGLETON = 'singleton';
    const CONFIG_LAZY = 'lazy';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $compiledMethod;

    /**
     * @var string
     */
    public $className;

    /** @var string */
    public $proxyClassNameOrInterface;

    /**
     * @var array
     */
    public $arguments = [];

    /**
     * @var array
     */
    public $methods = [];

    /**
     * @var \Closure
     */
    public $factory;

    /**
     * @var bool
     */
    public $isFactory = false;

    /**
     * @var boolean
     */
    public $isSingleton = true;

    /**
     * @var bool
     */
    public $isLazy = false;

    /**
     * Definition constructor.
     * @param string $name
     * @param array|\Closure $config
     * @throws ContainerException
     */
    public function __construct($name, $config)
    {
        $this->ensureNameIsNotEmpty($name);
        $this->ensureConfigIsArrayOrClosure($config);

        $this->name = \ltrim($name, '\\');
        $this->compiledMethod = 'create_' . str_replace('\\', '_', $this->name);
        $className = class_exists($name) ? $name : '';
        $interfaceName = interface_exists($name) ? $name : '';
        $classNameOrInterface = $className ?: $interfaceName;

        if ($config instanceof \Closure) {
            $this->className = $className;
            $this->proxyClassNameOrInterface = $classNameOrInterface;
            $this->isFactory = true;
            $this->factory = $config;
            return;
        }

        $this->className = \ltrim($config[self::CONFIG_CLASS] ?? $className, '\\');
        $this->proxyClassNameOrInterface = \ltrim($config[self::CONFIG_CLASS] ?? $classNameOrInterface, '\\');
        $this->arguments = (array)($config[self::CONFIG_ARGS] ?? []);
        $this->methods = array_map(function ($args) {
            return (array)$args;
        }, (array)($config[self::CONFIG_METHODS] ?? []));
        $this->isSingleton = (bool)($config[self::CONFIG_SINGLETON] ?? $this->isSingleton);
        $this->isLazy = (bool)($config[self::CONFIG_LAZY] ?? $this->isLazy);

        if ($factoryDefinition = ($config[self::CONFIG_FACTORY] ?? null)) {
            $this->isFactory = true;
            $this->factory = $factoryDefinition;
        }
    }

    /**
     * @param mixed $name
     * @throws ContainerException
     */
    private function ensureNameIsNotEmpty($name): void
    {
        if (!is_string($name) || $name === '') {
            throw new ContainerException('Name cannot be empty');
        }
    }

    /**
     * @param mixed $definition
     * @throws ContainerException
     */
    private function ensureConfigIsArrayOrClosure($definition): void
    {
        if (!is_array($definition) && !$definition instanceof \Closure) {
            throw new ContainerException('Definition config must be closure or array');
        }
    }
}
