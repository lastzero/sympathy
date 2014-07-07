<?php

namespace Sympathy\Tests\Silex;

use TestTools\TestCase\UnitTestCase;
use Sympathy\Tests\Silex\Kernel\AppKernel;

/**
 * @author Michael Mayer <michael@liquidbytes.net>
 * @package Sympathy
 * @license MIT
 */
class KernelTest extends UnitTestCase
{
    /**
     * @var AppKernel
     */
    protected $app;

    public function setUp()
    {
        $this->app = new AppKernel('sympathy_test');
    }

    public function testGetName()
    {
        $result = $this->app->getName();
        $this->assertEquals('Kernel', $result);
    }

    public function testGetVersion()
    {
        $result = $this->app->getVersion();
        $this->assertEquals('1.0', $result);
    }

    public function testGetEnvironment()
    {
        $result = $this->app->getEnvironment();
        $this->assertEquals('sympathy_test', $result);
    }

    public function testGetCharset()
    {
        $result = $this->app->getCharset();
        $this->assertEquals('UTF-8', $result);
    }

    public function testGetKernelParameters()
    {
        $result = $this->app->getKernelParameters();
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('kernel.name', $result);
        $this->assertArrayHasKey('kernel.version', $result);
        $this->assertArrayHasKey('kernel.environment', $result);
        $this->assertArrayHasKey('kernel.debug', $result);
        $this->assertArrayHasKey('kernel.charset', $result);
        $this->assertArrayHasKey('kernel.root_dir', $result);
        $this->assertArrayHasKey('kernel.cache_dir', $result);
        $this->assertArrayHasKey('kernel.logs_dir', $result);
        $this->assertArrayHasKey('kernel.config_dir', $result);
    }
}