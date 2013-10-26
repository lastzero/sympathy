<?php

namespace Sympathy\Tests\Assetic\Filter;

use TestTools\TestCase\UnitTestCase;
use Sympathy\Assetic\Filter\CssOptimizeFilter;

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

    /**
     * @expectedException \LogicException
     */
    public function testGetCountsException()
    {
        $this->filter->getCounts();
    }
}