<?php

namespace Zp\Container\Tests\Container;

use PHPUnit\Framework\TestCase;
use ProxyManager\Proxy\VirtualProxyInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Zp\Container\Container;
use Zp\Container\ProxyFactory;
use Zp\Container\Tests\Fixtures\Foo;

class LazyProxyTest extends TestCase
{
    private $tmpDir;

    protected function setUp()
    {
        /** @noinspection NonSecureUniqidUsageInspection */
        $this->tmpDir = sys_get_temp_dir() . '/' . uniqid('lazy-proxy-');
        mkdir($this->tmpDir);
    }

    protected function tearDown()
    {
        $it = new RecursiveDirectoryIterator($this->tmpDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($this->tmpDir);
    }

    public function testLazy()
    {
        // arrange
        $definitions = [
            Foo::class => ['lazy' => true]
        ];
        $container = new Container($definitions, new ProxyFactory($this->tmpDir));
        // act
        $entry = $container->get(Foo::class);
        // assert
        $this->assertInstanceOf(VirtualProxyInterface::class, $entry);
    }
}
