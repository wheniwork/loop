<?php
namespace Wheniwork\Feedback;

use Auryn\Injector;
use Negotiation\NegotiatorInterface;
use Spark\Resolver\ResolverInterface;

class Configuration
{
    public function apply(Injector $injector, array $env)
    {
        // --------------------
        // Initialize responders
        // --------------------
        $injector->delegate("\Spark\Responder\FormattedResponder", function(NegotiatorInterface $negotiator, ResolverInterface $resolver) {
            $responder = new \Spark\Responder\FormattedResponder($negotiator, $resolver);
            $responder = $responder->withFormatters([
                'Spark\Formatter\JsonFormatter' => 1.0
            ]);
            return $responder;
        });

        // --------------------
        // Initialize services
        // --------------------
        $services = "Wheniwork\Feedback\Service";

        $injector->define("$services\Authorizer", [
            ':key' => $env['POST_KEY']
        ]);

        $injector->define("$services\AppStoreService", [
            ':app_id' => $env['WIW_IOS_APP_ID']
        ]);

        $injector->define("\HieuLe\WordpressXmlrpcClient\WordpressClient", [
            ':xmlrpcEndPoint' => "http://wheniwork.com/blog/xmlrpc.php",
            ':username' => $env['WP_USER'],
            ':password' => $env['WP_PASSWORD']
        ]);
        $injector->define("$services\BlogService", [
            ':users' => preg_split('/\s*,\s*/', $env['WP_FEEDBACK_USERS'])
        ]);

        $injector->define("Facebook\Facebook", [
            ':config' => [
                'app_id' => $env['FB_APP_ID'],
                'app_secret' => $env['FB_APP_SECRET'],
                'default_graph_version' => 'v2.4'
            ]
        ]);
        $injector->define("$services\FacebookService", [
            ':page_id' => $env['FB_PAGE_ID']
        ]);
        $injector->prepare("$services\FacebookService", function($service) use ($env) {
            $service->authenticate(
                $env['FB_APP_ID'],
                $env['FB_APP_SECRET']
            );
        });

        $injector->prepare("\Github\Client", function($client) use ($env) {
            $client->authenticate($env['GITHUB_TOKEN'], "", $client::AUTH_HTTP_TOKEN);
        });

        $injector->define("$services\GooglePlayStoreService", [
            ':app_id' => $env['ANDROID_APP_ID']
        ]);

        $injector->define("$services\HipChatService", [
            ':key' => $env['HIPCHAT_KEY'],
            ':room' => $env['HIPCHAT_ROOM']
        ]);

        $injector->define("$services\SatismeterService", [
            ':key' => $env['SATISMETER_KEY'],
            ':product_id' => $env['SATISMETER_PRODUCT_ID']
        ]);

        $injector->define("TwitterAPIExchange", [
            ':settings' => [
                'oauth_access_token' => $env['TWITTER_OAUTH_TOKEN'],
                'oauth_access_token_secret' => $env['TWITTER_OAUTH_TOKEN_SECRET'],
                'consumer_key' => $env['TWITTER_CONSUMER_TOKEN'],
                'consumer_secret' => $env['TWITTER_CONSUMER_TOKEN_SECRET']
            ]
        ]);
        $injector->define("$services\TwitterService", [
            ':screen_name' => $env['TWITTER_WATCH_NAME']
        ]);
    }    
}
