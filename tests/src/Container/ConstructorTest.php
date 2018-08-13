<?php

namespace Zp\PHPWire\Tests\Container;

use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Container;
use Zp\PHPWire\Danilson;
use Zp\PHPWire\Tests\Fixtures\ClassDependency;
use Zp\PHPWire\Tests\Fixtures\InterfaceDependency;
use Zp\PHPWire\Tests\Fixtures\ScalarDependency;
use Zp\PHPWire\Tests\Fixtures\Foo;
use Zp\PHPWire\Tests\Fixtures\FooInterface;

class ConstructorTest extends TestCase
{
    public function testEmpty()
    {
        // arrange
        $container = new Container([
            Foo::class => [],
        ]);
        // act
        $entry = $container->get(Foo::class);
        // assert
        $this->assertInstanceOf(Foo::class, $entry);
    }

    public function testClass()
    {
        // arrange
        $container = new Container([
            Foo::class => [],
            ClassDependency::class => [
                'args' => [Foo::class]
            ],
        ]);
        // act
        /** @var ClassDependency $entry */
        $entry = $container->get(ClassDependency::class);
        // assert
        $this->assertInstanceOf(ClassDependency::class, $entry);
        $this->assertInstanceOf(Foo::class, $entry->getFoo());
    }

    public function testNamedService()
    {
        // arrange
        $container = new Container([
            'foo' => ['class' => Foo::class],
            ClassDependency::class => [
                'args' => ['$foo']
            ],
        ]);
        // act
        /** @var ClassDependency $entry */
        $entry = $container->get(ClassDependency::class);
        // assert
        $this->assertInstanceOf(ClassDependency::class, $entry);
        $this->assertInstanceOf(Foo::class, $entry->getFoo());
    }

    public function testInterface()
    {
        // arrange
        $container = new Container([
            FooInterface::class => [
                'class' => Foo::class,
            ],
            InterfaceDependency::class => [
                'args' => [FooInterface::class]
            ],
        ]);
        // act
        /** @var ClassDependency $entry */
        $entry = $container->get(InterfaceDependency::class);
        // assert
        $this->assertInstanceOf(InterfaceDependency::class, $entry);
        $this->assertInstanceOf(Foo::class, $entry->getFoo());
    }

    /**
     * @param array $spec
     * @dataProvider everythingDataProvider
     */
    public function testEverything(array $spec): void
    {
        $container = new Container($this->getFixtureDefinitionsBySpec($spec));
        $this->assertInstanceOf(Container::class, $container);
        $this->assertContractBySpec($spec, $container);
    }

    public function everythingDataProvider(): array
    {
        // @todo Find specs by implemented interfaces.
        $definitionSpecs = $this->cartesian([
            'as' => [
                Danilson\Definition\AsIs::class,
            ],
            'with' => [
                null,
                Danilson\Definition\WithDependency::class,

            ],
        ]);
        $dependencySpecs = $this->cartesian([
            'as' => [
                Danilson\Definition\Dependency\AsIs::class,
            ],
            'with' => [
                null,
                Danilson\Definition\Dependency\WithNotExists::class,

            ],
        ]);
        $keySpecs = [Danilson\Key\AsClass::class, Danilson\Key\AsInterface::class, Danilson\Key\AsStringAlias::class];
        $valueSpecs = [Danilson\Value\AsEmpty::class, Danilson\Value\ClassName\AsIs::class];

        $combinations = $this->cartesian([
            'definition' => $definitionSpecs,
            'key' => $keySpecs,
            'value' => $valueSpecs,
            'dependency' => $dependencySpecs,
            'dependencyKey' => $keySpecs,
            'dependencyValue' => $valueSpecs,
        ]);
        $combinations = array_unique(array_map(function ($item) {
            if ($item['definition']['with'] === null) {
                unset($item['dependency']);
                unset($item['dependencyKey']);
                unset($item['dependencyValue']);
            }
            return json_encode($item);
        }, $combinations));
        $specs = array_map(function ($item) { return ['spec' => json_decode($item, true)]; }, $combinations);
        return $specs;
    }

    private function getFixtureDefinitionsBySpec($spec): array
    {
        $result = [];
        $result[$this->getFixtureKeyBySpec($spec['key'])] = $this->getFixtureValueBySpec($spec['value'], $spec);
        if ($spec['dependency'] ?? false) {
            $result[$this->getFixtureKeyBySpec($spec['dependencyKey'])] = $this->getFixtureValueBySpec($spec['dependencyValue'], $spec);
        }
        return $result;
    }

    private function getFixtureKeyBySpec($spec): string
    {
        switch ($spec) {
            case Danilson\Key\AsClass::class:
                return Foo::class;
            case Danilson\Key\AsInterface::class:
                return FooInterface::class;
            case Danilson\Key\AsStringAlias::class:
                return 'foo';
            default:
                throw new \InvalidArgumentException($spec);
        }
    }

    private function getFixtureValueBySpec(string $specValue, array $spec): array
    {
        switch (true) {
            case ($spec['dependency']['with'] ?? null) == Danilson\Definition\Dependency\WithNotExists::class:
                return ['class' => 'bar'];
            case Danilson\Value\ClassName\AsIs::class == $specValue:
                return ['class' => Foo::class];
            case Danilson\Value\AsEmpty::class == $specValue:
                return [];
            // @todo Add WithAbsract for AsClass.
            // @todo Add WithExtends for AsClass.
            // @todo Add WithExtendedBy for AsClass.
            // @todo Add AsInterface.
            // @todo Add WithNotImplements for AsInterface.
            default:
                throw new \InvalidArgumentException($spec);
        }
    }

    // @link https://stackoverflow.com/a/15973172
    private function cartesian($input) {
        $result = array(array());

        foreach ($input as $key => $values) {
            $append = array();

            foreach ($result as $product) {
                foreach ($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }

            $result = $append;
        }

        return $result;
    }

    private function assertContractBySpec($spec, Container $container): void
    {
        // @todo Refactor with mock and shouldThrowException.
        if (
            ($spec['dependency']['with'] ?? null) === Danilson\Definition\Dependency\WithNotExists::class
            &&
            $spec['dependencyValue'] !== Danilson\Value\AsEmpty::class

        ) {
            throw new \LogicException(print_r([
                'error' => 'Should throw InvalidArgumentException',
                'spec' => $spec,
                'container' => $container,
            ], true));
        }
    }
}
