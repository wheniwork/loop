<?php

define('APP_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

require APP_PATH . 'vendor/autoload.php';

// Load .env file and configure injector
$loader = new josegonzalez\Dotenv\Loader(__DIR__ . '/../.env');
$env = $loader->parse()->toArray();

$injector = new Auryn\Injector;

$config = new Wheniwork\Feedback\Configuration;
$config->apply($injector, $env);

// Boot app
$app = Spark\Application::boot($injector);
$app->getRouter()->setDefaultResponder('Spark\Responder\JsonResponder');

// Add routes
$app->addRoutes(function(Spark\Router $r) {
    $ns = 'Wheniwork\Feedback';

    $r->get('/twitter', "$ns\Domain\GetTwitter");
    $r->get('/facebook', "$ns\Domain\GetFacebook");
    $r->get('/blog', "$ns\Domain\GetBlog");
    $r->get('/satismeter', "$ns\Domain\GetSatismeter");
    $r->get('/appstore', "$ns\Domain\GetAppStore");
    $r->get('/googleplay', "$ns\Domain\GetGooglePlayStore");

    $r->post('/post', "$ns\Domain\DoGeneric");
    $r->post('/zendesk', "$ns\Domain\DoZendesk");
    $r->post('/manager', "$ns\Domain\DoManagerTool");
});

date_default_timezone_set('UTC');

$app->run();
