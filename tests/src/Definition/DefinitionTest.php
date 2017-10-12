<?php

namespace Zp\PHPWire\Tests\Definition;

use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Definition;

class DefinitionTest extends TestCase
{
    /**
     * @expectedException \Zp\PHPWire\ContainerException
     * @expectedExceptionMessage Definition config must be closure or array
     */
    public function testConfigMustBeClosureOrArray()
    {
        // arrange
        $config = null;
        // act
        new Definition('foobar', $config);
    }

    public function testArrayAsConfig()
    {
        // arrange
        $config = [];
        // act
        $definition = new Definition('foobar', $config);
        // assert
        $this->assertEquals('foobar', $definition->name);
        $this->assertCount(0, $definition->arguments);
        $this->assertCount(0, $definition->methods);
        $this->assertNull($definition->factory);
        $this->assertTrue($definition->isSingleton);
        $this->assertFalse($definition->isFactory);
        $this->assertFalse($definition->isLazy);
    }

    public function testClosureAsConfig()
    {
        // arrange
        $config = function () {
        };
        // act
        $definition = new Definition('foobar', $config);
        // assert
        $this->assertEquals('foobar', $definition->name);
        $this->assertCount(0, $definition->arguments);
        $this->assertCount(0, $definition->methods);
        $this->assertSame($config, $definition->factory);
        $this->assertTrue($definition->isSingleton);
        $this->assertTrue($definition->isFactory);
        $this->assertFalse($definition->isLazy);
    }

    public function testEmptyConfig()
    {
        // arrange
        $config = [];
        // act
        $definition = new Definition('foobar', $config);
        // assert
        $this->assertCount(0, $definition->arguments);
        $this->assertCount(0, $definition->methods);
        $this->assertNull($definition->factory);
        $this->assertTrue($definition->isSingleton);
        $this->assertFalse($definition->isFactory);
        $this->assertFalse($definition->isLazy);
    }

    public function testSingleton()
    {
        // arrange
        $config = [
            'singleton' => false,
        ];
        // act
        $definition = new Definition('foobar', $config);
        // assert
        $this->assertFalse($definition->isSingleton);
    }

    public function testLazy()
    {
        // arrange
        $config = [
            'lazy' => true,
        ];
        // act
        $definition = new Definition('foobar', $config);
        // assert
        $this->assertTrue($definition->isSingleton);
    }

    public function testArgs()
    {
        // arrange
        $config = [
            'args' => [true, \stdClass::class],
        ];
        // act
        $definition = new Definition('foobar', $config);
        // assert
        $this->assertCount(2, $definition->arguments);
        $this->assertEquals([true, \stdClass::class], $definition->arguments);
    }

    public function testMethods()
    {
        // arrange
        $config = [
            'methods' => [
                'someMethod' => [true, 'arg' => \stdClass::class]
            ],
        ];
        // act
        $definition = new Definition('foobar', $config);
        // assert
        $this->assertCount(1, $definition->methods);
        $this->assertArrayHasKey('someMethod', $definition->methods);
        $this->assertEquals([true, 'arg' => \stdClass::class], $definition->methods['someMethod']);
    }
}
