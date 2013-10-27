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

    public function testGetCounts()
    {
        $expected = array (
            'skipped' => 0,
            'merged' => 2,
            'properties' => 9,
            'selectors' => 5,
            'nested' => 1,
            'unoptimized' => 1,
        );

        $inputCss = '@font { foo: bar; }body { foo:bar; too: me;} body { baz:foo; } body { foo: new }
        div.test { border: 1px solid black; }
        div.other { border: 1px solid black}
        font { { face: 123; } }';

        $this->optimizer->optimizeCss($inputCss);

        $result = $this->optimizer->getCounts();
        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \Sympathy\Css\Exception
     */
    public function testGetCountsException()
    {
        $this->optimizer->getCounts();
    }

    /**
     * @expectedException \Sympathy\Css\Exception
     * @expectedExceptionMessage Input CSS must not contain comments
     */
    public function testOptimizeCssCommentException() {
        $inputCss = 'body { foo:bar; too: me;} /* comment */ body { baz:foo; } body { foo: new }';

        $this->optimizer->optimizeCss($inputCss);
    }

    public function testOptimizeCss() {
        $expected = 'body{baz:foo;foo:new;too:me}div.other,div.test{border:1px solid black}';

        $inputCss = 'body { foo:bar; too: me;} body { baz:foo; } body { foo: new }
        div.test { border: 1px solid black; }
        div.other { border: 1px solid black} ';

        $result = $this->optimizer->optimizeCss($inputCss);

        $this->assertEquals($expected, $result);
    }

    public function testMinifyCss() {
        $expected = 'body{foo:bar;too:me}body{baz:foo}body{foo:new}div.test{border:1px solid black}div.other{border:1px solid black}';

        $inputCss = 'body { foo:bar; too: me;} body { baz:foo; } body { foo: new }
        div.test { border: 1px solid black; }
        div.other { border: 1px solid black} ';

        $result = $this->optimizer->minifyCss($inputCss);

        $this->assertEquals($expected, $result);
    }

    public function testMinifyAndOptimizeCss() {
        $expected = 'body{baz:foo;foo:new;too:me}div.other,div.test{border:1px solid black}';

        $inputCss = 'body { foo:bar; too: me;} /* Foo Bar Test */ body { baz:foo; } body { foo: new }
        div.test { border: 1px solid black; }
        div.other { border: 1px solid black} ';

        $result = $this->optimizer->optimizeCss($this->optimizer->minifyCss($inputCss));

        $this->assertEquals($expected, $result);
    }
}