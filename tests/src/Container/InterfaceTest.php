<?php

namespace Zp\Container\Tests\Container;

use PHPUnit\Framework\TestCase;
use Zp\Container\Container;

class InterfaceTest extends TestCase
{
    public function testHas()
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

    public function testGet()
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

    public function testGetDoesMemoizeByDefaultInsteadOfPSR4()
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

    public function testGetDoesNotMemoize()
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

    /**
     * @expectedException \Psr\Container\NotFoundExceptionInterface
     * @expectedExceptionMessage Requested a non-existent container entry `dummy`
     */
    public function testGetThrowsOnUndefinedEntry()
    {
        $container = new Container([
            'foo' => function () {
                return 'bar';
            },
            'bar' => function () {
                return 'baz';
            },
        ]);
        $container->get('dummy');
    }
}
