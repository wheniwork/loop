<?php

define('APP_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

require APP_PATH . 'vendor/autoload.php';

$injector = new Auryn\Injector;
$injector->define('Predis\Client', []);

$app = Spark\Application::boot($injector);
$app->getRouter()->setDefaultResponder('Spark\Responder\JsonResponder');

$app->addRoutes(function(Spark\Router $r) {
    $ns = 'Wheniwork\Feedback';

    // Add routes here
});

$app->run();
