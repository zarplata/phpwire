<?php

namespace Zp\PHPWire\Tests\Container;

use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Container;

class InterfaceTest extends TestCase
{
    public function testHas(): void
    {
        // assert
        $definitions = [
            'foo' => function () {
                return 'bar';
            },
            'bar' => function () {
                return 'baz';
            },
        ];
        // act
        $container = new Container($definitions);
        // assert
        $this->assertTrue($container->has('foo'));
        $this->assertTrue($container->has('bar'));
        $this->assertFalse($container->has('dummy'));
    }

    public function testGet(): void
    {
        // arrange
        $definitions = [
            'foo' => function () {
                return 'bar';
            },
            'bar' => function () {
                return 'baz';
            },
        ];
        // act
        $container = new Container($definitions);
        // assert
        $this->assertSame('bar', $container->get('foo'));
        $this->assertSame('baz', $container->get('bar'));
    }

    public function testGetDoesMemoizeByDefaultInsteadOfPSR4(): void
    {
        // arrange
        $i = 0;
        $definitions = [
            'foo' => function () use (&$i) {
                ++$i;
                return 'bar';
            },
        ];
        // act
        $container = new Container($definitions);
        // assert
        $this->assertSame('bar', $container->get('foo'));
        $this->assertSame('bar', $container->get('foo'));
        $this->assertSame(1, $i);
    }

    public function testGetDoesNotMemoize(): void
    {
        // arrange
        $i = 0;
        $definitions = [
            'foo' => [
                'singleton' => false,
                'factory' => function () use (&$i) {
                    ++$i;
                    return 'bar';
                }
            ],
        ];
        // act
        $container = new Container($definitions);
        // assert
        $this->assertSame('bar', $container->get('foo'));
        $this->assertSame('bar', $container->get('foo'));
        $this->assertSame(2, $i);
    }

    public function testGetThrowsOnUndefinedEntry(): void
    {
        // arrange
        $container = new Container([
            'foo' => function () {
                return 'bar';
            },
            'bar' => function () {
                return 'baz';
            },
        ]);

        // assert
        $this->expectExceptionMessage("Requested a non-existent container entry `dummy`");
        $this->expectException(\Psr\Container\NotFoundExceptionInterface::class);

        // act
        $container->get('dummy');
    }
}
