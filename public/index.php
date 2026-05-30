<?php
require_once __DIR__."/../vendor/autoload.php";

use DI\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Views\PhpRenderer;
error_reporting(E_ALL & ~E_USER_NOTICE);
   
session_start();

// Create Container using PHP-DI
$container = new Container();

// Add custom response factory
$container->set(ResponseFactoryInterface::class, fn() => new ResponseFactory());
$container->set(PhpRenderer::class, function() {
    $renderer = new PhpRenderer(__DIR__."/../src/Views");
    $renderer->setLayout("layout.phtml");
    return $renderer;
});


// Configure the application via container
$app = AppFactory::createFromContainer($container);
$errorMiddleware = $app->addErrorMiddleware(true, false, false);

(require_once __DIR__."/../src/routes.php")($app);

$app->run();
