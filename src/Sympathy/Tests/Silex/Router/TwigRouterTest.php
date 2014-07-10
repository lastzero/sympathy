<?php

namespace Sympathy\Tests\Silex\Router;

use TestTools\TestCase\UnitTestCase;
use Sympathy\Silex\Router\TwigRouter;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class TwigRouterTest extends UnitTestCase
{
    /**
     * @var TwigRouter
     */
    protected $router;

    public function setUp()
    {
        $this->router = $this->get('router.twig');
    }

    public function testRoute () {
        $this->router->route();
    }
}