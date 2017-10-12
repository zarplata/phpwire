<?php

namespace Zp\PHPWire\Tests\Caching;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zp\PHPWire\ContainerCompiler;
use Zp\PHPWire\Definition;
use Zp\PHPWire\Tests\Fixtures\ClassDependency;
use Zp\PHPWire\Tests\Fixtures\Foo;
use Zp\PHPWire\Tests\Fixtures\ScalarDependency;

class ContainerCompilerTest extends TestCase
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
namespace Zp\PHPWire;

class CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

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
namespace Zp\PHPWire;

class CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

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
namespace Zp\PHPWire;

class CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
    {
        $instance = new \Zp\PHPWire\Tests\Fixtures\ClassDependency(call_user_func(function(ContainerInterface $container) {return new Foo();}, $container));
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
namespace Zp\PHPWire;

class CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
    {
        $instance = new \Zp\PHPWire\Tests\Fixtures\ClassDependency($container->get(\'Zp\PHPWire\Tests\Fixtures\Foo\'));
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
namespace Zp\PHPWire;

class CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_Zp_PHPWire_Tests_Fixtures_ScalarDependency($container)
    {
        $instance = new \Zp\PHPWire\Tests\Fixtures\ScalarDependency(123);
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
namespace Zp\PHPWire;

class CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
    {
        $instance = new \Zp\PHPWire\Tests\Fixtures\ClassDependency(123);
        $instance->setFoo($container->get(\'Zp\PHPWire\Tests\Fixtures\Foo\'))
        return $instance;
    }


}
', $builder->compile());
    }
}
