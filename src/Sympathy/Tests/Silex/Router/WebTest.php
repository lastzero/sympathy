<?php

namespace Sympathy\Tests\Silex\Router;

use TestTools\TestCase\UnitTestCase;
use Sympathy\Silex\Router\Web;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class WebTest extends UnitTestCase
{
    /**
     * @var Web
     */
    protected $router;

    public function setUp()
    {
        $this->router = $this->get('router.web');
    }

    public function testRoute () {
        $this->router->route();
    }
}