<?php

namespace Sympathy\Silex\Router;

class Error extends Router
{
    protected $exceptionCodes = array();

    public function setExceptionCodes (array $exceptionCodes) {
        $this->exceptionCodes = $exceptionCodes;
    }

    public function route($errorWebControllerService = 'controller.web.error', $errorRestControllerService = 'controller.rest.error')
    {
        $app = $this->app;
        $container = $this->container;
        $exceptionCodes = $this->exceptionCodes;

        $app->error(function (\Exception $e, $code) use ($app, $container, $exceptionCodes, $errorWebControllerService, $errorRestControllerService) {
            $request = $app['request'];
            $exceptionClass = get_class($e);

            if (isset($exceptionCodes[$exceptionClass])) {
                $code = $exceptionCodes[$exceptionClass];
            }

            if (0 === strpos($request->headers->get('Accept'), 'application/json')) {
                try {
                    $controller = $container->get($errorRestControllerService);
                    return $controller->errorAction($e, $code);
                } catch (\Exception $e) {
                }
            }

            $controller = $container->get($errorWebControllerService);

            return $controller->errorAction($e, $code);
        });
    }
}