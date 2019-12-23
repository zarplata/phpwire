<?php

namespace Zp\PHPWire\Tests\Definition;

use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Definition;
use Zp\PHPWire\Tests\Fixtures\Foo;
use Zp\PHPWire\Tests\Fixtures\FooInterface;

class NamingTest extends TestCase
{
    /**
     * @dataProvider providerOfEmptyDefinitions
     * @param mixed $config
     */
    public function testClassNameAsDefinitionName($config)
    {
        // act
        $definition = new Definition(Foo::class, $config);
        // assert
        $this->assertEquals(Foo::class, $definition->className);
        $this->assertEquals(Foo::class, $definition->proxyClassNameOrInterface);
    }

    /**
     * @dataProvider providerOfEmptyDefinitions
     * @param mixed $config
     */
    public function testInterfaceNameAsDefinitionName($config)
    {
        // act
        $definition = new Definition(FooInterface::class, $config);
        // assert
        $this->assertEmpty($definition->className);
        $this->assertEquals(FooInterface::class, $definition->proxyClassNameOrInterface);
    }

    /**
     * @dataProvider providerOfEmptyDefinitions
     * @param mixed $config
     */
    public function testSimpleStringAsDefinitionName($config)
    {
        // act
        $definition = new Definition('foobar', $config);
        // assert
        $this->assertEmpty($definition->className);
        $this->assertEmpty($definition->proxyClassNameOrInterface);
    }

    public function testConfigClassNameOverrideDefinitionName()
    {
        // arrange
        $config = [
            'class' => \stdClass::class,
        ];
        // act
        $definition = new Definition(Foo::class, $config);
        // assert
        $this->assertEquals(\stdClass::class, $definition->className);
        $this->assertEquals(\stdClass::class, $definition->proxyClassNameOrInterface);
    }

    /**
     * @return array
     */
    public function providerOfEmptyDefinitions()
    {
        return [
            'empty closure' => [
                function () {
                },
            ],
            'empty array' => [
                []
            ],
        ];
    }
}
