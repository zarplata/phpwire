<?php

namespace Zp\Container\Tests\Fixtures;

class InterfaceDependency
{
    /**
     * @var Foo
     */
    private $foo;

    /**
     * @param FooInterface $foo
     */
    public function __construct(FooInterface $foo = null)
    {
        $this->foo = $foo;
    }

    /**
     * @return Foo
     */
    public function getFoo()
    {
        return $this->foo;
    }
}
