<?php

namespace Zp\PHPWire\Tests\Caching;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Zp\PHPWire\ContainerCompiler;
use Zp\PHPWire\Definition;
use Zp\PHPWire\Tests\Fixtures\ClassDependency;
use Zp\PHPWire\Tests\Fixtures\Foo;
use Zp\PHPWire\Tests\Fixtures\MagicMethod;
use Zp\PHPWire\Tests\Fixtures\ScalarDependency;

class ContainerCompilerTest extends TestCase
{
    public function testStdClass(): void
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $definition = new Definition(stdClass::class, []);
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        self::assertEquals(
            <<<'PHP'
                <?php
                class Zp_PHPWire_CompiledContainer
                {
                    use \Zp\PHPWire\ContainerAwareTrait;
                
                    public function create_stdClass($container)
                    {
                        $instance = new \stdClass();
                        return $instance;
                    }
                }

                PHP,
            $compiler->compile()
        );
    }

    public function testFactory(): void
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $definition = new Definition(
            stdClass::class,
            [
                'factory' => function () {
$instance = new stdClass();
$instance->asd = 'asd';
return $instance;
                }
            ]
        );
        // act
        $compiler->addDefinition($definition, $container);
        $compiled = $compiler->compile();

        // assert
        $expected = <<<'PHP'
                <?php
                class Zp_PHPWire_CompiledContainer
                {
                    use \Zp\PHPWire\ContainerAwareTrait;
                
                    public function create_stdClass($container)
                    {
                        $f = unserialize(base64_decode(
                            'BASE64-HERE'
                        ));
                        return $f($container);
                    }
                }
                
                PHP;

        self::assertEquals($expected, preg_replace('#\'(\\w+)\'#i', '\'BASE64-HERE\'', $compiled), $compiled);
    }

    public function testCtorClosureArgument(): void
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $definition = new Definition(
            ClassDependency::class,
            [
                'args' => [
                    'foo' => function (ContainerInterface $c) {
                        return new Foo();
                    }
                ]
            ]
        );
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        self::assertEquals(
            <<<'PHP'
                <?php
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
                
                PHP,
            $compiler->compile()
        );
    }

    public function testCtorContainerArgument(): void
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $definition = new Definition(ClassDependency::class, []);
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        self::assertEquals(
            <<<'PHP'
                <?php
                class Zp_PHPWire_CompiledContainer
                {
                    use \Zp\PHPWire\ContainerAwareTrait;
                
                    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
                    {
                        $instance = new \Zp\PHPWire\Tests\Fixtures\ClassDependency($container->get('Zp\PHPWire\Tests\Fixtures\Foo'));
                        return $instance;
                    }
                }

                PHP,
            $compiler->compile()
        );
    }

    public function testCtorScalarArgument()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->method('has')->willReturn(true);

        $definition = new Definition(
            ScalarDependency::class,
            [
                'args' => [123]
            ]
        );
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        self::assertEquals(
            <<<'PHP'
                <?php
                class Zp_PHPWire_CompiledContainer
                {
                    use \Zp\PHPWire\ContainerAwareTrait;
                
                    public function create_Zp_PHPWire_Tests_Fixtures_ScalarDependency($container)
                    {
                        $instance = new \Zp\PHPWire\Tests\Fixtures\ScalarDependency(123);
                        return $instance;
                    }
                }
                
                PHP,
            $compiler->compile()
        );
    }

    public function testMagicMethod()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->method('has')->willReturn(true);

        $definition = new Definition(
            MagicMethod::class,
            [
                'methods' => [
                    'setFoo' => ['value']
                ],
            ]
        );
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        self::assertEquals(
            <<<'PHP'
                <?php
                class Zp_PHPWire_CompiledContainer
                {
                    use \Zp\PHPWire\ContainerAwareTrait;
                
                    public function create_Zp_PHPWire_Tests_Fixtures_MagicMethod($container)
                    {
                        $instance = new \Zp\PHPWire\Tests\Fixtures\MagicMethod();
                        $instance->setFoo($container->get('value'));
                        return $instance;
                    }
                }
                
                PHP,
            $compiler->compile()
        );
    }

    public function testMethodArgument()
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->method('has')->willReturn(true);

        $definition = new Definition(
            ClassDependency::class,
            [
                'args' => [123],
                'methods' => [
                    'setFoo' => null
                ],
            ]
        );
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        self::assertEquals(
            <<<'PHP'
                <?php
                class Zp_PHPWire_CompiledContainer
                {
                    use \Zp\PHPWire\ContainerAwareTrait;
                
                    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
                    {
                        $instance = new \Zp\PHPWire\Tests\Fixtures\ClassDependency(123);
                        $instance->setFoo($container->get('Zp\PHPWire\Tests\Fixtures\Foo'));
                        return $instance;
                    }
                }
                
                PHP,
            $compiler->compile()
        );
    }

    public function testClassNameTrailingSlash(): void
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->method('has')->willReturn(true);

        $definition = new Definition(
            '\\' . ClassDependency::class,
            [
                'args' => [123],
                'methods' => [
                    'setFoo' => null
                ],
            ]
        );
        // act
        $compiler->addDefinition($definition, $container);
        // assert
        self::assertEquals(
            <<<'PHP'
                <?php
                class Zp_PHPWire_CompiledContainer
                {
                    use \Zp\PHPWire\ContainerAwareTrait;
                
                    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
                    {
                        $instance = new \Zp\PHPWire\Tests\Fixtures\ClassDependency(123);
                        $instance->setFoo($container->get('Zp\PHPWire\Tests\Fixtures\Foo'));
                        return $instance;
                    }
                }
                
                PHP,
            $compiler->compile()
        );
    }

    public function testClosureDefinition(): void
    {
        // arrange
        $compiler = new ContainerCompiler();
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->method('has')->willReturn(true);

        $definition = new Definition(
            '\\' . ClassDependency::class,
            [
                'factory' => function (ContainerInterface $c) {
                    return new ClassDependency($c->get('foo'));
                }
            ]
        );
        // act
        $compiler->addDefinition($definition, $container);
        $compiled = $compiler->compile();

        // assert
        $expected = <<<'PHP'
                <?php
                class Zp_PHPWire_CompiledContainer
                {
                    use \Zp\PHPWire\ContainerAwareTrait;
                
                    public function create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container)
                    {
                        $f = unserialize(base64_decode(
                            'BASE64-HERE'
                        ));
                        return $f($container);
                    }
                }
                
                PHP;

        self::assertEquals($expected, preg_replace('#\'([a-zA-Z0-9=+]+)\'#i', '\'BASE64-HERE\'', $compiled), $compiled);
    }

    public function testEvaluate(): void
    {
        // arrange
        $container = $this->getMockForAbstractClass(ContainerInterface::class);
        $container->method('has')->willReturn(true);

        $compiler = new ContainerCompiler();
        $compiler->addDefinition(
            new Definition(
                '\\' . ClassDependency::class,
                [
                    'factory' => function (ContainerInterface $c) {
                        return new ClassDependency($c->get('foo'));
                    }
                ]
            ),
            $container
        );
        $compiler->addDefinition(
            new Definition(
                stdClass::class,
                [
                    'factory' => function () {
                        $instance = new stdClass();
                        $instance->asd = 'asd';
                        return $instance;
                    }
                ]
            ),
            $container
        );

        // act
        $compiled = $compiler->compile();
        eval(str_replace('<?php', '', $compiled));
        $compiled = new \Zp_PHPWire_CompiledContainer();

        $dep1 = $compiled->create_Zp_PHPWire_Tests_Fixtures_ClassDependency($container);
        $dep2 = $compiled->create_stdClass($container);

        // assert
        self::assertInstanceOf(ClassDependency::class, $dep1);
        self::assertInstanceOf(stdClass::class, $dep2);
    }
}
