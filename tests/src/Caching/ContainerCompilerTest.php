<?php

namespace Zp\Container\Tests\Caching;

use Psr\Container\ContainerInterface;
use Zp\Container\ContainerCompiler;
use Zp\Container\Definition;
use Zp\Container\Tests\Fixtures\ClassDependency;
use Zp\Container\Tests\Fixtures\Foo;
use Zp\Container\Tests\Fixtures\ScalarDependency;

class ContainerCompilerTest extends \PHPUnit_Framework_TestCase
{
    public function testStdClass()
    {
        // arrange
        $builder = new ContainerCompiler();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $definition = new Definition(\stdClass::class, []);
        // act
        $builder->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
namespace Zp\Container;

class CompiledContainer
{

    use \Zp\Container\ContainerAwareTrait;

    public function create_stdClass($container)
    {
        $instance = new \stdClass();
        return $instance;
    }


}
', $builder->compile());
    }

    public function testFactory()
    {
        // arrange
        $builder = new ContainerCompiler();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $definition = new Definition(\stdClass::class, [
            'factory' => function () {
                $instance = new \stdClass();
                $instance->asd = 'asd';
                return $instance;
            }
        ]);
        // act
        $builder->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
namespace Zp\Container;

class CompiledContainer
{

    use \Zp\Container\ContainerAwareTrait;

    public function create_stdClass($container)
    {
        $instance = new \stdClass();
        $instance->asd = \'asd\';
        return $instance;
    }


}
', $builder->compile());
    }

    public function testCtorClosureArgument()
    {
        // arrange
        $builder = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->willReturn(true);
        $definition = new Definition(ClassDependency::class, [
            'args' => [
                'foo' => function () {
                    return new Foo();
                }
            ]
        ]);
        // act
        $builder->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
namespace Zp\Container;

class CompiledContainer
{

    use \Zp\Container\ContainerAwareTrait;

    public function create_Zp_Container_Tests_Fixtures_ClassDependency($container)
    {
        $instance = new \Zp\Container\Tests\Fixtures\ClassDependency(call_user_func(function(ContainerInterface $container) {return new Foo();}, $container));
        return $instance;
    }


}
', $builder->compile());
    }

    public function testCtorContainerArgument()
    {
        // arrange
        $builder = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->willReturn(true);
        $definition = new Definition(ClassDependency::class, []);
        // act
        $builder->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
namespace Zp\Container;

class CompiledContainer
{

    use \Zp\Container\ContainerAwareTrait;

    public function create_Zp_Container_Tests_Fixtures_ClassDependency($container)
    {
        $instance = new \Zp\Container\Tests\Fixtures\ClassDependency($container->get(\'Zp\Container\Tests\Fixtures\Foo\'));
        return $instance;
    }


}
', $builder->compile());
    }

    public function testCtorScalarArgument()
    {
        // arrange
        $builder = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->willReturn(true);

        $definition = new Definition(ScalarDependency::class, [
            'args' => [123]
        ]);
        // act
        $builder->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
namespace Zp\Container;

class CompiledContainer
{

    use \Zp\Container\ContainerAwareTrait;

    public function create_Zp_Container_Tests_Fixtures_ScalarDependency($container)
    {
        $instance = new \Zp\Container\Tests\Fixtures\ScalarDependency(123);
        return $instance;
    }


}
', $builder->compile());
    }

    public function testMethodArgument()
    {
        // arrange
        $builder = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->willReturn(true);

        $definition = new Definition(ClassDependency::class, [
            'args' => [123],
            'methods' => [
                'setFoo' => null
            ],
        ]);
        // act
        $builder->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
namespace Zp\Container;

class CompiledContainer
{

    use \Zp\Container\ContainerAwareTrait;

    public function create_Zp_Container_Tests_Fixtures_ClassDependency($container)
    {
        $instance = new \Zp\Container\Tests\Fixtures\ClassDependency(123);
        $instance->setFoo($container->get(\'Zp\Container\Tests\Fixtures\Foo\'))
        return $instance;
    }


}
', $builder->compile());
    }
}
