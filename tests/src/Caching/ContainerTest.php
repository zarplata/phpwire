<?php

namespace Zp\Container\Tests\Caching;

use PHPUnit\Framework\TestCase;
use Zp\Container\Container;
use Zp\Container\Tests\Fixtures\Foo;

class ContainerTest extends TestCase
{
    public function testGenerateCacheFile()
    {
        // arrange
        $cacheFile = sys_get_temp_dir() . '/' . uniqid('container-', true);
        $definitions = [
            Foo::class => [],
        ];
        $container = new Container($definitions, null, $cacheFile);
        // act
        $container->compileContainer();
        // assert
        $this->assertStringEqualsFile($cacheFile, '<?php
namespace Zp\Container;

class CompiledContainer
{

    use \Zp\Container\ContainerAwareTrait;

    public function create_Zp_Container_Tests_Fixtures_Foo($container)
    {
        $instance = new \Zp\Container\Tests\Fixtures\Foo();
        return $instance;
    }


}
');
    }

    public function testUsingCacheFile()
    {
        // arrange
        $definitions = [
            Foo::class => [],
        ];
        $container = new Container($definitions, null);

        $mock = $this->getMockBuilder('Zp\Container\CompiledContainer')
            ->setMethods(['create_Zp_Container_Tests_Fixtures_Foo'])
            ->getMock();
        $mock->expects($this->once())
            ->method('create_Zp_Container_Tests_Fixtures_Foo')
            ->with($container)
            ->willReturn(new Foo());

        $this->injectCompiledContainerMock($container, $mock);
        // act
        $foo = $container->get(Foo::class);
        // assert
        $this->assertInstanceOf(Foo::class, $foo);
    }

    private function injectCompiledContainerMock(Container $container, \PHPUnit_Framework_MockObject_MockObject $mock)
    {
        $method = new \ReflectionProperty(Container::class, 'compiledContainer');
        $method->setAccessible(true);
        $method->setValue($container, $mock);
    }
}
