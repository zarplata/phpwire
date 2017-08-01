<?php

namespace Zp\Container;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;

/**
 * Создает прокси классы proxy classes. Обертка над Ocramius/ProxyManager.
 *
 * @category    Job
 * @package     Zp\Dic
 * @author      Vladimir Komissarov <v.komissarov@office.ngs.ru>
 * @author      Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ProxyFactory
{
    /**
     * Directory where to write the proxies (if $writeProxiesToFile is enabled).
     * @var string
     */
    private $_proxyDirectory;

    /**
     * @var LazyLoadingValueHolderFactory
     */
    private $_proxyManager;

    public function __construct($proxyDirectory)
    {
        $this->_proxyDirectory = $proxyDirectory;
    }

    /**
     * Creates a new lazy proxy instance of the given class with
     * the given initializer.
     *
     * @param string   $className   name of the class to be proxied
     * @param \Closure $initializer initializer to be passed to the proxy
     *
     * @return \ProxyManager\Proxy\LazyLoadingInterface
     */
    public function createProxy($className, \Closure $initializer)
    {
        $this->createProxyManager();
        return $this->_proxyManager->createProxy($className, $initializer);
    }

    public function generateProxy($className)
    {
        $this->createProxyManager();
        $this->_proxyManager->createProxy($className, function () {
        });
    }

    private function createProxyManager()
    {
        if ($this->_proxyManager !== null) {
            return;
        }
        $config = new Configuration();
        $config->setProxiesTargetDir($this->_proxyDirectory);
        spl_autoload_register($config->getProxyAutoloader());
        $this->_proxyManager = new LazyLoadingValueHolderFactory($config);
    }
}
