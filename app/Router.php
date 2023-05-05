<?php declare(strict_types=1);

namespace App;

use App\Views\View;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    private array $routes;

    public function __construct()
    {
        $this->routes = require_once 'routes.php';
    }

    public function response(): ?View
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $router) {
            foreach ($this->routes as $route) {
                [$httpMethod, $url, $handler] = $route;
                $router->addRoute($httpMethod, $url, $handler);
            }
        });

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                header('Location: /error/404');
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                header('Location: /error/405');
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                [$controllerName, $methodName] = $handler;
                $controller = new $controllerName;
                return $controller->{$methodName}($vars);
        }
        return null;
    }
}
