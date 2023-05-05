<?php declare(strict_types=1);

require_once '../vendor/autoload.php';

$router = new \App\Router();
$renderer = new \App\Renderer();

echo $renderer->render($router->response());
