<?php

namespace Sympathy\Silex\Router;

use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

class Web extends Router
{
    protected $twig;

    public function setTwig (Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function route($routePrefix = '', $servicePrefix = 'controller.web.', $servicePostfix = '')
    {
        $app = $this->app;
        $container = $this->container;

        $webRequestHandler = function ($controller, Request $request, $action = '') use ($app, $container, $servicePrefix, $servicePostfix) {
            if (!$action) {
                $action = 'index';
            }

            $prefix = strtolower($request->getMethod());
            $parts = explode('/', $action);

            $subResources = '';
            $params = array();

            $count = count($parts);

            for ($i = 0; $i < $count; $i++) {
                $subResources .= ucfirst($parts[$i]);

                if (isset($parts[$i + 1])) {
                    $i++;
                    $params[] = $parts[$i];
                }
            }

            $params[] = $request;
            $actionName = $prefix . $subResources . 'Action';

            $controllerService = $servicePrefix . strtolower($controller) . $servicePostfix;

            try{
                $controllerInstance = $container->get($controllerService);
            } catch (\Exception $e) {
                throw new NotFoundException ('Web controller service not found: ' . $controllerService);
            }

            if ($prefix == 'get' && !method_exists($controllerInstance, $actionName)) {
                $actionName = $subResources . 'Action';
            }

            if (!method_exists($controllerInstance, $actionName)) {
                throw new NotFoundException ('Web controller method not found: ' . $actionName);
            }

            if($this->twig) {
                $this->twig->addGlobal('controller', $controller);
                $this->twig->addGlobal('action', $subResources);
            }

            $result = call_user_func_array(array($controllerInstance, $actionName), $params);

            return $result;
        };

        $indexRequestHandler = function (Request $request) use ($app, $container, $servicePrefix, $servicePostfix, $webRequestHandler) {
            return $webRequestHandler('index', $request, 'index');
        };

        $app->get($routePrefix . '/', $indexRequestHandler);
        $app->match($routePrefix . '/{controller}', $webRequestHandler);
        $app->match($routePrefix . '/{controller}/{action}', $webRequestHandler)->assert('action', '.+');
    }
}