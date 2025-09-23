<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

(require __DIR__ . '/../src/bootstrap.php')($app);
(require __DIR__ . '/../src/routes.php')($app);

$errorMiddleware = $app->addErrorMiddleware((bool) ($_ENV['APP_DEBUG'] ?? false), true, true);

$app->run();
