<?php

define('APP_PATH', realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR);

use josegonzalez\Dotenv\Loader as EnvLoader;
use Facebook\Facebook;
use \Github\Client as GithubClient;
use TwitterAPIExchange;
use \HieuLe\WordpressXmlrpcClient\WordpressClient;

require APP_PATH . 'vendor/autoload.php';

$services = 'Wheniwork\Feedback\Service';

// Initialize Injector
$injector = new Auryn\Injector;
$injector->define('Predis\Client', []);
$injector->define(EnvLoader::class, [':filepaths' => __DIR__ . '/../.env']);
$injector->prepare(EnvLoader::class, function(EnvLoader $loader) {
    $loader->parse()->putenv(true);
});
$injector->delegate("$services\AppStoreService", function() {
    return new Wheniwork\Feedback\Service\AppStoreService(
        getenv('WIW_IOS_APP_ID')
    );
});
$injector->delegate("$services\BlogService", function() {
    $client = new WordpressClient(
        "http://wheniwork.com/blog/xmlrpc.php",
        getenv('WP_USER'),
        getenv('WP_PASSWORD')
    );
    return new Wheniwork\Feedback\Service\BlogService($client);
});
$injector->delegate("$services\FacebookService", function() {
    $fb = new Facebook([
        'app_id' => getenv('FB_APP_ID'),
        'app_secret' => getenv('FB_APP_SECRET'),
        'default_graph_version' => 'v2.4'
    ]);
    $service = new Wheniwork\Feedback\Service\FacebookService($fb, getenv('FB_PAGE_ID'));
    $service->authenticate(
        getenv('FB_APP_ID'),
        getenv('FB_APP_SECRET')
    );
    return $service;
});
$injector->delegate("$services\GithubService", function() {
    $client = new GithubClient;
    $client->authenticate(getenv('GITHUB_TOKEN'), "", GithubClient::AUTH_HTTP_TOKEN);
    return new Wheniwork\Feedback\Service\GithubService($client);
});
$injector->delegate("$services\HipChatService", function() {
    return new Wheniwork\Feedback\Service\HipChatService(
        getenv('HIPCHAT_KEY'),
        getenv('HIPCHAT_ROOM')
    );
});
$injector->delegate("$services\SatismeterService", function() {
    return new Wheniwork\Feedback\Service\SatismeterService(
        getenv('SATISMETER_KEY'),
        getenv('SATISMETER_PRODUCT_ID')
    );
});
$injector->delegate("$services\TwitterService", function() {
    $twitter = new TwitterAPIExchange([
        'oauth_access_token' => getenv('TWITTER_OAUTH_TOKEN'),
        'oauth_access_token_secret' => getenv('TWITTER_OAUTH_TOKEN_SECRET'),
        'consumer_key' => getenv('TWITTER_CONSUMER_TOKEN'),
        'consumer_secret' => getenv('TWITTER_CONSUMER_TOKEN_SECRET')
    ]);
    return new Wheniwork\Feedback\Service\TwitterService($twitter);
});

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

    $r->post('/post', "$ns\Domain\DoGeneric");
    $r->post('/zendesk', "$ns\Domain\DoZendesk");
    $r->post('/manager', "$ns\Domain\DoManagerTool");
});

date_default_timezone_set('UTC');

$app->run();
