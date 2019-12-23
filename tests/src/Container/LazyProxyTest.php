<?php

namespace Zp\PHPWire\Tests\Container;

use PHPUnit\Framework\TestCase;
use ProxyManager\Proxy\VirtualProxyInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Zp\PHPWire\Container;
use Zp\PHPWire\ContainerException;
use Zp\PHPWire\ProxyFactory;
use Zp\PHPWire\Tests\Fixtures\Foo;
use Zp\PHPWire\Tests\Fixtures\FooInterface;

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

    public function testLazyForConcreteClass()
    {
        // arrange
        $definitions = [
            Foo::class => ['lazy' => true]
        ];
        $container = new Container($definitions, new ProxyFactory($this->tmpDir));
        // act
        /** @var Foo $entry */
        $entry = $container->get(Foo::class);
        // assert
        $this->assertInstanceOf(VirtualProxyInterface::class, $entry);
        $this->assertEquals('its foo class', $entry->test());
    }

    public function testLazyForInterface()
    {
        // arrange
        $definitions = [
            FooInterface::class => [
                'factory' => static function () {
                    return new Foo();
                },
                'lazy' => true,
            ]
        ];
        $container = new Container($definitions, new ProxyFactory($this->tmpDir));
        // act
        /** @var FooInterface $entry */
        $entry = $container->get(FooInterface::class);
        // assert
        $this->assertInstanceOf(VirtualProxyInterface::class, $entry);
        $this->assertEquals('its foo class', $entry->test());
    }

    public function testLazyForInterfaceWithClassName()
    {
        // arrange
        $definitions = [
            FooInterface::class => [
                'class' => Foo::class,
                'lazy' => true,
            ]
        ];
        $container = new Container($definitions, new ProxyFactory($this->tmpDir));

        // act
        /** @var Foo $foo */
        $foo = $container->get(FooInterface::class);

        // assert
        $this->assertInstanceOf(Foo::class, $foo);
    }

    public function testLazyForInterfaceWithoutFactory()
    {
        // arrange
        $definitions = [
            FooInterface::class => [
                'lazy' => true,
            ]
        ];
        $container = new Container($definitions, new ProxyFactory($this->tmpDir));

        // act
        try {
            $container->get(FooInterface::class);
            $this->fail('Failed asserting that exception of type "Zp\PHPWire\ContainerException" is thrown');
        } catch (ContainerException $e) {
            $this->assertInstanceOf(ContainerException::class, $e->getPrevious());
            $this->assertEquals(
                'Definition of interface entry `Zp\PHPWire\Tests\Fixtures\FooInterface` must have concrete class name or factory',
                $e->getPrevious()->getMessage()
            );
        }
    }
}
