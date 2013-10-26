<?php

namespace Sympathy\Tests\Assetic\Filter;

use TestTools\TestCase\UnitTestCase;
use Sympathy\Assetic\Filter\CssOptimizeFilter;
use Assetic\Asset\StringAsset;
use Assetic\FilterManager;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class CssOptimizeFilterTest extends UnitTestCase
{
    /**
     * @var CssOptimizeFilter
     */
    protected $filter;

    public function setUp()
    {
        $this->filter = new CssOptimizeFilter;
    }

    public function testFilterDumpWithFilterManager() {
        $filterManager = new FilterManager();
        $filterManager->set('css_optimize', $this->filter);

        $expected = 'body{baz:foo;foo:new;too:me}div.other,div.test{border:1px solid black}';

        $inputCss = 'body { foo:bar; too: me;} body { baz:foo; } body { foo: new }
        div.test { border: 1px solid black; }
        div.other { border: 1px solid black} ';

        $asset = new StringAsset($inputCss);
        $asset->ensureFilter($filterManager->get('css_optimize'));
        $result = $asset->dump();

        $this->assertEquals($expected, $result);
    }

    public function testFilterDump() {
        $expected = 'body{baz:foo;foo:new;too:me}div.other,div.test{border:1px solid black}';

        $inputCss = 'body { foo:bar; too: me;} body { baz:foo; } body { foo: new }
        div.test { border: 1px solid black; }
        div.other { border: 1px solid black} ';

        $asset = new StringAsset($inputCss);
        $asset->ensureFilter($this->filter);
        $result = $asset->dump();

        $this->assertEquals($expected, $result);
    }
}