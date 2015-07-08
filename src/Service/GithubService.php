<?php
namespace Wheniwork\Feedback\Service;

use \Github\Client as GithubClient;

class GithubService
{
    private static $client;

    private static function getClient()
    {
        if (empty(self::$client)) {
            self::$client = new GithubClient;
            self::$client->authenticate($_ENV['GITHUB_TOKEN'], "", GithubClient::AUTH_HTTP_TOKEN);
        }
        
        return self::$client;
    }

    public static function createIssue($title, $body)
    {
        $client = self::getClient();
        return $client->api('issue')->create(
            'wheniwork',
            'loop-feed',
            [
                'title' => $title,
                'body' => $body
            ]
        );
    }
}
