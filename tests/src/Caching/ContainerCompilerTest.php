<?php

namespace Zp\PHPWire\Tests\Caching;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zp\PHPWire\ContainerCompiler;
use Zp\PHPWire\Definition;
use Zp\PHPWire\Tests\Fixtures\ClassDependency;
use Zp\PHPWire\Tests\Fixtures\Foo;
use Zp\PHPWire\Tests\Fixtures\MagicMethod;
use Zp\PHPWire\Tests\Fixtures\ScalarDependency;

class ContainerCompilerTest extends TestCase
{
    public function testStdClass()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $definition = new Definition(\stdClass::class, []);
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
class Zp_PHPWire_CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_stdClass($container)
    {
        $instance = new \stdClass();
        return $instance;
    }


}
', $compiler->compile());
    }

    public function testFactory()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $definition = new Definition(\stdClass::class, [
            'factory' => function () {
                $instance = new \stdClass();
                $instance->asd = 'asd';
                return $instance;
            }
        ]);
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
class Zp_PHPWire_CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_stdClass($container)
    {
        $f = function () {
            $instance = new \stdClass();
            $instance->asd = \'asd\';
            return $instance;
        };
        return call_user_func($f, $container);
    }


}
', $compiler->compile());
    }

    public function testCtorClosureArgument()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->willReturn(true);
        $definition = new Definition(ClassDependency::class, [
            'args' => [
                'foo' => function (ContainerInterface $c) {
                    return new Foo();
                }
            ]
        ]);
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
class Zp_PHPWire_CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
    {
        $instance = new \Zp\PHPWire\Tests\Fixtures\ClassDependency(call_user_func(function (\Psr\Container\ContainerInterface $c) {
            return new \Zp\PHPWire\Tests\Fixtures\Foo();
        }, $container));
        return $instance;
    }


}
', $compiler->compile());
    }

    public function testCtorContainerArgument()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->willReturn(true);
        $definition = new Definition(ClassDependency::class, []);
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
class Zp_PHPWire_CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
    {
        $instance = new \Zp\PHPWire\Tests\Fixtures\ClassDependency($container->get(\'Zp\PHPWire\Tests\Fixtures\Foo\'));
        return $instance;
    }


}
', $compiler->compile());
    }

    public function testCtorScalarArgument()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->willReturn(true);

        $definition = new Definition(ScalarDependency::class, [
            'args' => [123]
        ]);
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
class Zp_PHPWire_CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_Zp_PHPWire_Tests_Fixtures_ScalarDependency($container)
    {
        $instance = new \Zp\PHPWire\Tests\Fixtures\ScalarDependency(123);
        return $instance;
    }


}
', $compiler->compile());
    }

    public function testMagicMethod()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->willReturn(true);

        $definition = new Definition(MagicMethod::class, [
            'methods' => [
                'setFoo' => ['value']
            ],
        ]);
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
class Zp_PHPWire_CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_Zp_PHPWire_Tests_Fixtures_MagicMethod($container)
    {
        $instance = new \Zp\PHPWire\Tests\Fixtures\MagicMethod();
        $instance->setFoo($container->get(\'value\'));
        return $instance;
    }


}
', $compiler->compile());
    }

    public function testMethodArgument()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->willReturn(true);

        $definition = new Definition(ClassDependency::class, [
            'args' => [123],
            'methods' => [
                'setFoo' => null
            ],
        ]);
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
class Zp_PHPWire_CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
    {
        $instance = new \Zp\PHPWire\Tests\Fixtures\ClassDependency(123);
        $instance->setFoo($container->get(\'Zp\PHPWire\Tests\Fixtures\Foo\'));
        return $instance;
    }


}
', $compiler->compile());
    }

    public function testClassNameTrailingSlash()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->willReturn(true);

        $definition = new Definition('\\' . ClassDependency::class, [
            'args' => [123],
            'methods' => [
                'setFoo' => null
            ],
        ]);
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
class Zp_PHPWire_CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
    {
        $instance = new \Zp\PHPWire\Tests\Fixtures\ClassDependency(123);
        $instance->setFoo($container->get(\'Zp\PHPWire\Tests\Fixtures\Foo\'));
        return $instance;
    }


}
', $compiler->compile());
    }

    public function testClosureDefinition()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->expects($this->any())->method('has')->willReturn(true);

        $definition = new Definition('\\' . ClassDependency::class, [
            'factory' => function(ContainerInterface $c) {
                return new ClassDependency($c->get('foo'));
            }
        ]);
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        $this->assertEquals('<?php
class Zp_PHPWire_CompiledContainer
{

    use \Zp\PHPWire\ContainerAwareTrait;

    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
    {
        $f = function (\Psr\Container\ContainerInterface $c) {
            return new \Zp\PHPWire\Tests\Fixtures\ClassDependency($c->get(\'foo\'));
        };
        return call_user_func($f, $container);
    }


}
', $compiler->compile());
    }
}
