<?php

require __DIR__ . '/../vendor/autoload.php';

use Wheniwork\Feedback;

date_default_timezone_set('UTC');

Spark\Application::build()
->setConfiguration([
    Spark\Configuration\AurynConfiguration::class,
    Spark\Configuration\DiactorosConfiguration::class,
    Spark\Configuration\PayloadConfiguration::class,
    Spark\Configuration\RelayConfiguration::class,
    Spark\Configuration\WhoopsConfiguration::class,
    Spark\Configuration\EnvConfiguration::class,
    Wheniwork\Feedback\Configuration::class,
])
->setMiddleware([
    Relay\Middleware\ResponseSender::class,
    Spark\Handler\ExceptionHandler::class,
    Spark\Handler\DispatchHandler::class,
    Spark\Handler\JsonContentHandler::class,
    Spark\Handler\ActionHandler::class,
])
->setRouting(function (Spark\Directory $directory) {
    return $directory

    ->get('/twitter', Feedback\Domain\GetTwitter::class)
    ->get('/facebook', Feedback\Domain\GetFacebook::class)
    ->get('/blog', Feedback\Domain\GetBlog::class)
    ->get('/satismeter', Feedback\Domain\GetSatismeter::class)
    ->get('/appstore', Feedback\Domain\GetAppStore::class)
    ->get('/googleplay', Feedback\Domain\GetGooglePlayStore::class)

    ->post('/post', Feedback\Domain\DoGeneric::class)
    ->post('/zendesk', Feedback\Domain\DoZendesk::class)
    ->post('/manager', Feedback\Domain\DoManagerTool::class)

    ;
})
->run();
