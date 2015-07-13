<?php

define('APP_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

require APP_PATH . 'vendor/autoload.php';

$injector = new Auryn\Injector;
$injector->define('Predis\Client', []);

$app = Spark\Application::boot($injector);
$app->getRouter()->setDefaultResponder('Spark\Responder\JsonResponder');

$app->addRoutes(function(Spark\Router $r) {
    $ns = 'Wheniwork\Feedback';

    $r->get('/twitter', "$ns\Domain\GetTwitter");
    $r->get('/facebook', "$ns\Domain\GetFacebook");
});

$app->run();
