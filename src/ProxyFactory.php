<?php

namespace Zp\PHPWire;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\Proxy\LazyLoadingInterface;

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

    public function __construct(string $proxyDirectory)
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
     * @return LazyLoadingInterface
     */
    public function createProxy(string $className, \Closure $initializer): LazyLoadingInterface
    {
        $this->createProxyManager();
        return $this->proxyManager->createProxy($className, $initializer);
    }

    /**
     * @param string $className
     * @return void
     */
    public function generateProxy(string $className): void
    {
        $this->createProxyManager();
        $this->proxyManager->createProxy($className, function () {
        });
    }

    /**
     * @return void
     */
    private function createProxyManager(): void
    {
        if ($this->proxyManager !== null) {
            return;
        }
        $config = new Configuration();
        $config->setProxiesTargetDir($this->proxyDirectory);
        $config->setGeneratorStrategy(new ProxyFileWriterGeneratorStrategy(new FileLocator($this->proxyDirectory)));
        spl_autoload_register($config->getProxyAutoloader());
        $this->proxyManager = new LazyLoadingValueHolderFactory($config);
    }
}
