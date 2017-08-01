<?php

namespace Zp\Container\Tests\Definition;

use Zp\Container\Definition;
use Zp\Container\Tests\Fixtures\Foo;
use Zp\Container\Tests\Fixtures\FooInterface;

class NamingTest extends \PHPUnit_Framework_TestCase
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
