<?php

require __DIR__ . '/../vendor/autoload.php';

use Wheniwork\Feedback;

date_default_timezone_set('UTC');

Equip\Application::build()
->setConfiguration([
    Equip\Configuration\AurynConfiguration::class,
    Equip\Configuration\DiactorosConfiguration::class,
    Equip\Configuration\PayloadConfiguration::class,
    Equip\Configuration\RelayConfiguration::class,
    Equip\Configuration\WhoopsConfiguration::class,
    Equip\Configuration\EnvConfiguration::class,
    Wheniwork\Feedback\Configuration::class,
])
->setMiddleware([
    Relay\Middleware\ResponseSender::class,
    Equip\Handler\ExceptionHandler::class,
    Equip\Handler\DispatchHandler::class,
    Equip\Handler\JsonContentHandler::class,
    Equip\Handler\ActionHandler::class,
])
->setRouting(function (Equip\Directory $directory) {
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
