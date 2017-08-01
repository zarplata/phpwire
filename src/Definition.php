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
        $className = class_exists($name) ? $name : null;

        if ($config instanceof \Closure) {
            $this->className = $className;
            $this->isFactory = true;
            $this->factory = $config;
            return;
        }

        $this->className = self::parseConfig(self::CONFIG_CLASS, $config, $className);
        $this->arguments = (array)self::parseConfig(self::CONFIG_ARGS, $config, []);
        $this->methods = self::parseMethods($config);
        $this->isSingleton = (bool)self::parseConfig(self::CONFIG_SINGLETON, $config, $this->isSingleton);
        $this->isLazy = (bool)self::parseConfig(self::CONFIG_LAZY, $config, $this->isLazy);

        if ($factoryDefinition = self::parseConfig(self::CONFIG_FACTORY, $config)) {
            $this->isFactory = true;
            $this->factory = $factoryDefinition;
        }
    }

    /**
     * @param string $name
     * @param array $definition
     * @param null $default
     * @return mixed|null
     */
    private static function parseConfig($name, array $definition, $default = null)
    {
        return array_key_exists($name, $definition) ? $definition[$name] : $default;
    }

    /**
     * @param array $config
     * @return array
     * @throws ContainerException
     */
    private static function parseMethods(array $config)
    {
        $methods = self::parseConfig(self::CONFIG_METHODS, $config, []);
        if (!is_array($methods)) {
            throw new ContainerException('Methods definition should be array');
        }
        $result = [];
        foreach ($methods as $name => $args) {
            if (!$name) {
                throw new ContainerException('Name of method cannot be empty');
            }
            if (!is_array($args)) {
                throw new ContainerException("Arguments of method {$name} should be array");
            }
            $result[$name] = $args;
        }
        return $result;
    }

    /**
     * @param mixed $name
     * @throws ContainerException
     */
    private function ensureNameIsNotEmpty($name)
    {
        if (!is_string($name) || $name === '') {
            throw new ContainerException('Name cannot be empty');
        }
    }

    /**
     * @param mixed $definition
     * @throws ContainerException
     */
    private function ensureConfigIsArrayOrClosure($definition)
    {
        if (!is_array($definition) && !$definition instanceof \Closure) {
            throw new ContainerException('Definition config must be closure or array');
        }
    }
}
