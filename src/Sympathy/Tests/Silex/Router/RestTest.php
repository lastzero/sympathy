<?php

namespace Sympathy\Tests\Silex\Router;

use TestTools\TestCase\UnitTestCase;
use Sympathy\Silex\Router\Rest;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class RestTest extends UnitTestCase
{
    /**
     * @var Rest
     */
    protected $router;

    public function setUp()
    {
        $this->router = $this->get('router.rest');
    }

    public function testRoute () {
        $this->router->route();
    }
}