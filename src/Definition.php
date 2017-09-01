<?php

namespace Zp\Container;

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

        $this->name = $name;
        $this->compiledMethod = 'create_' . str_replace('\\', '_', $name);
        $className = class_exists($name) ? $name : null;

        if ($config instanceof \Closure) {
            $this->className = $className;
            $this->isFactory = true;
            $this->factory = $config;
            return;
        }

        $this->className = $config[self::CONFIG_CLASS] ?? $className;
        $this->arguments = (array)($config[self::CONFIG_ARGS] ?? []);
        $this->methods = array_map(function ($args) {
            return (array)$args;
        }, (array)($config[self::CONFIG_METHODS] ?? []));
        $this->isSingleton = (bool)($config[self::CONFIG_SINGLETON] ?? $this->isSingleton);
        $this->isLazy = (bool)($config[self::CONFIG_LAZY] ?? $this->isLazy);

        if ($factoryDefinition = $config[self::CONFIG_FACTORY]) {
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
