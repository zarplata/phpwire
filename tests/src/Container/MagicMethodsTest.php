<?php

namespace Zp\PHPWire\Tests\Definition;

use PHPUnit\Framework\TestCase;
use Zp\PHPWire\Container;
use Zp\PHPWire\Tests\Fixtures\MagicMethod;

class MagicMethodsTest extends TestCase
{
    public function testMagicMethod()
    {
        // arrange
        $container = new Container([
            MagicMethod::class => [
                'methods' => [
                    'test' => ['asd']
                ]
            ]
        ]);
        // act
        $entry = $container->get(MagicMethod::class);
        // assert
        $this->assertEquals(['test' => ['asd']], $entry->get());
    }
}
