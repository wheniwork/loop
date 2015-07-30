<?php
namespace Wheniwork\Feedback\Service;

use \Github\Client as GithubClient;

class GithubService
{
    private $client;

    public function __construct(GithubClient $client)
    {
        $this->client = $client;
    }

    public function createIssue($title, $body)
    {
        return $this->client->api('issue')->create(
            'wheniwork',
            'loop-feed',
            [
                'title' => $title,
                'body' => $body
            ]
        );
    }
}
