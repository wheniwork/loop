<?php
namespace Wheniwork\Feedback;

use Auryn\Injector;
use Equip\Configuration\ConfigurationInterface;
use Equip\Env;

class Configuration implements ConfigurationInterface
{
    private $env;

    public function __construct(Env $env) {
        $this->env = $env;
    }

    public function apply(Injector $injector)
    {
        // --------------------
        // Initialize responders
        // --------------------
        $injector->prepare("Equip\Responder\FormattedResponder", function($responder, $injector) {
            $responder = $responder->withData([
                'Equip\Formatter\JsonFormatter' => 1.0
            ]);
            return $responder;
        });

        // --------------------
        // Initialize PDO
        // --------------------
        $injector->define("Aura\Sql\ExtendedPdo", [
            ':dsn' => $this->env['DB_DSN'],
            ':username' => $this->env['DB_USERNAME'],
            ':password' => $this->env['DB_PASSWORD']
        ]);
        $injector->define("Aura\SqlQuery\QueryFactory", [
            ':db' => $this->env['DB_TYPE']
        ]);

        // --------------------
        // Initialize services
        // --------------------
        $services = "Wheniwork\Feedback\Service";

        $injector->define("$services\Authorizer", [
            ':key' => $this->env['POST_KEY']
        ]);

        $injector->define("$services\AppStoreService", [
            ':app_id' => $this->env['WIW_IOS_APP_ID']
        ]);

        $injector->define("HieuLe\WordpressXmlrpcClient\WordpressClient", [
            ':xmlrpcEndPoint' => "http://wheniwork.com/blog/xmlrpc.php",
            ':username' => $this->env['WP_USER'],
            ':password' => $this->env['WP_PASSWORD']
        ]);
        $injector->define("$services\BlogService", [
            ':users' => preg_split('/\s*,\s*/', $this->env['WP_FEEDBACK_USERS'])
        ]);

        $injector->define("$services\DatabaseService", [
            ':tableName' => $this->env['DB_TABLE']
        ]);

        $injector->define("Facebook\Facebook", [
            ':config' => [
                'app_id' => $this->env['FB_APP_ID'],
                'app_secret' => $this->env['FB_APP_SECRET'],
                'default_graph_version' => 'v2.4'
            ]
        ]);
        $injector->define("$services\FacebookService", [
            ':page_id' => $this->env['FB_PAGE_ID']
        ]);
        $injector->prepare("$services\FacebookService", function($service) {
            $service->authenticate(
                $this->env['FB_APP_ID'],
                $this->env['FB_APP_SECRET']
            );
        });

        $injector->define("$services\GooglePlayStoreService", [
            ':app_id' => $this->env['ANDROID_APP_ID']
        ]);

        $injector->define("$services\HipChatService", [
            ':key' => $this->env['HIPCHAT_KEY'],
            ':room' => $this->env['HIPCHAT_ROOM']
        ]);

        $injector->define("$services\SatismeterService", [
            ':key' => $this->env['SATISMETER_KEY'],
            ':product_id' => $this->env['SATISMETER_PRODUCT_ID']
        ]);

        $injector->define("TwitterAPIExchange", [
            ':settings' => [
                'oauth_access_token' => $this->env['TWITTER_OAUTH_TOKEN'],
                'oauth_access_token_secret' => $this->env['TWITTER_OAUTH_TOKEN_SECRET'],
                'consumer_key' => $this->env['TWITTER_CONSUMER_TOKEN'],
                'consumer_secret' => $this->env['TWITTER_CONSUMER_TOKEN_SECRET']
            ]
        ]);
        $injector->define("$services\TwitterService", [
            ':screen_name' => $this->env['TWITTER_WATCH_NAME']
        ]);
    }    
}
