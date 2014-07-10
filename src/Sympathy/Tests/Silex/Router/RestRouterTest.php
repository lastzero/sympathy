<?php

namespace Sympathy\Tests\Silex\Router;

use TestTools\TestCase\UnitTestCase;
use Sympathy\Silex\Router\RestRouter;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class RestRouterTest extends UnitTestCase
{
    /**
     * @var RestRouter
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