<?php

namespace Zp\Container\Tests\Fixtures;

class ClassDependency
{
    /**
     * @var Foo
     */
    private $foo;

    /**
     * @param Foo $foo
     */
    public function __construct(Foo $foo = null)
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
