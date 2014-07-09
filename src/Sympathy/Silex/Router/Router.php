<?php

namespace Sympathy\Silex\Router;

use Silex\Application;
use Symfony\Component\DependencyInjection\Container;

abstract class Router {
    protected $app;
    protected $container;

    public function __construct(Application $app, Container $container) {
        $this->app = $app;
        $this->container = $container;
    }
}