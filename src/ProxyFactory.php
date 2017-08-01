<?php

namespace Zp\Container;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * Wrapper for `ocramius/proxy-manager`.
 */
class ProxyFactory
{
    /**
     * Directory where to write the proxies
     * @var string
     */
    private $proxyDirectory;

    /**
     * @var LazyLoadingValueHolderFactory
     */
    private $proxyManager;

    public function __construct($proxyDirectory)
    {
        $this->proxyDirectory = $proxyDirectory;
    }

    /**
     * Creates a new lazy proxy instance of the given class with
     * the given initializer.
     *
     * @param string $className name of the class to be proxied
     * @param \Closure $initializer initializer to be passed to the proxy
     *
     * @return \ProxyManager\Proxy\LazyLoadingInterface
     */
    public function createProxy($className, \Closure $initializer)
    {
        $this->createProxyManager();
        return $this->proxyManager->createProxy($className, $initializer);
    }

    /**
     * @param string $className
     * @return void
     */
    public function generateProxy($className)
    {
        $this->createProxyManager();
        $this->proxyManager->createProxy($className, function () {
        });
    }

    /**
     * @return void
     */
    private function createProxyManager()
    {
        if ($this->proxyManager !== null) {
            return;
        }
        $config = new Configuration();
        $config->setProxiesTargetDir($this->proxyDirectory);
        spl_autoload_register($config->getProxyAutoloader());
        $this->proxyManager = new LazyLoadingValueHolderFactory($config);
    }
}
