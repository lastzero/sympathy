<?php

namespace Sympathy\Tests\Silex\Router;

use TestTools\TestCase\UnitTestCase;
use Sympathy\Silex\Router\Error;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class ErrorTest extends UnitTestCase
{
    /**
     * @var Error
     */
    protected $router;

    public function setUp()
    {
        $this->router = $this->get('router.error');
    }

    public function testRoute () {
        $this->router->route();
    }
}