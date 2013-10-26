<?php

namespace Sympathy\Tests\Css;

use TestTools\TestCase\UnitTestCase;
use Sympathy\Css\Optimizer;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class OptimizerTest extends UnitTestCase
{
    /**
     * @var Optimizer
     */
    protected $optimizer;

    public function setUp()
    {
        $this->optimizer = new Optimizer;
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetCountsException()
    {
        $this->optimizer->getCounts();
    }

    public function testOptimizeCss() {
        $expected = 'body{baz:foo;foo:new;too:me}div.other,div.test{border:1px solid black}';

        $inputCss = 'body { foo:bar; too: me;} body { baz:foo; } body { foo: new }
        div.test { border: 1px solid black; }
        div.other { border: 1px solid black} ';

        $result = $this->optimizer->optimizeCss($inputCss);

        $this->assertEquals($expected, $result);
    }
}