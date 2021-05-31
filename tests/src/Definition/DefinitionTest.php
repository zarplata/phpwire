<?php

namespace Zp\PHPWire\Tests\Definition;

use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Definition;

class DefinitionTest extends TestCase
{
    public function testConfigMustBeClosureOrArray(): void
    {
        // arrange
        $config = null;

        // assert
        $this->expectException(\Zp\PHPWire\ContainerException::class);
        $this->expectExceptionMessage('Definition config must be closure or array');

        // act
        new Definition('foobar', $config);
    }

    public function testArrayAsConfig(): void
    {
        // arrange
        $config = [];
        // act
        $definition = new Definition('foobar', $config);
        // assert
        self::assertEquals('foobar', $definition->name);
        self::assertCount(0, $definition->arguments);
        self::assertCount(0, $definition->methods);
        self::assertNull($definition->factory);
        self::assertTrue($definition->isSingleton);
        self::assertFalse($definition->isFactory);
        self::assertFalse($definition->isLazy);
    }

    public function testClosureAsConfig(): void
    {
        // arrange
        $config = function () {
        };
        // act
        $definition = new Definition('foobar', $config);
        // assert
        self::assertEquals('foobar', $definition->name);
        self::assertCount(0, $definition->arguments);
        self::assertCount(0, $definition->methods);
        self::assertSame($config, $definition->factory);
        self::assertTrue($definition->isSingleton);
        self::assertTrue($definition->isFactory);
        self::assertFalse($definition->isLazy);
    }

    public function testEmptyConfig(): void
    {
        // arrange
        $config = [];
        // act
        $definition = new Definition('foobar', $config);
        // assert
        self::assertCount(0, $definition->arguments);
        self::assertCount(0, $definition->methods);
        self::assertNull($definition->factory);
        self::assertTrue($definition->isSingleton);
        self::assertFalse($definition->isFactory);
        self::assertFalse($definition->isLazy);
    }

    public function testSingleton(): void
    {
        // arrange
        $config = [
            'singleton' => false,
        ];
        // act
        $definition = new Definition('foobar', $config);
        // assert
        self::assertFalse($definition->isSingleton);
    }

    public function testLazy(): void
    {
        // arrange
        $config = [
            'lazy' => true,
        ];
        // act
        $definition = new Definition('foobar', $config);
        // assert
        self::assertTrue($definition->isLazy);
    }

    public function testArgs(): void
    {
        // arrange
        $config = [
            'args' => [true, \stdClass::class],
        ];
        // act
        $definition = new Definition('foobar', $config);
        // assert
        self::assertCount(2, $definition->arguments);
        self::assertEquals([true, \stdClass::class], $definition->arguments);
    }

    public function testMethods(): void
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
        self::assertCount(1, $definition->methods);
        self::assertArrayHasKey('someMethod', $definition->methods);
        self::assertEquals([true, 'arg' => \stdClass::class], $definition->methods['someMethod']);
    }
}
