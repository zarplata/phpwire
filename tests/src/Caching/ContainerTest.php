<?php

namespace Zp\PHPWire\Tests\Caching;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Container;
use Zp\PHPWire\Tests\Fixtures\Foo;

class ContainerTest extends TestCase
{
    public function testGenerateCacheFile(): void
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
        self::assertStringEqualsFile(
            $cacheFile,
            <<<'PHP'
                <?php
                class Zp_PHPWire_CompiledContainer
                {
                    use \Zp\PHPWire\ContainerAwareTrait;
                
                    public function create_Zp_PHPWire_Tests_Fixtures_Foo($container)
                    {
                        $instance = new \Zp\PHPWire\Tests\Fixtures\Foo();
                        return $instance;
                    }
                }
                
                PHP
        );
    }

    public function testUsingCacheFile(): void
    {
        // arrange
        $definitions = [
            Foo::class => [],
        ];
        $container = new Container($definitions, null);

        $mock = $this->getMockBuilder('Zp_PHPWire_CompiledContainer')
            ->addMethods(['create_Zp_PHPWire_Tests_Fixtures_Foo'])
            ->getMock();
        $mock->expects($this->once())
            ->method('create_Zp_PHPWire_Tests_Fixtures_Foo')
            ->with($container)
            ->willReturn(new Foo());

        $this->injectCompiledContainerMock($container, $mock);
        // act
        $foo = $container->get(Foo::class);
        // assert
        $this->assertInstanceOf(Foo::class, $foo);
    }

    private function injectCompiledContainerMock(Container $container, MockObject $mock): void
    {
        $method = new \ReflectionProperty(Container::class, 'compiledContainer');
        $method->setAccessible(true);
        $method->setValue($container, $mock);
    }
}
