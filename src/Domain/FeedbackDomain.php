<?php
namespace Wheniwork\Feedback\Domain;

use Spark\Adr\DomainInterface;
use Aura\Payload\Payload;
use Predis\Client as RedisClient;
use Wheniwork\Feedback\Service\GithubService;
use Wheniwork\Feedback\Service\HipChatService;

abstract class FeedbackDomain implements DomainInterface
{
    protected $redis;

    const POSITIVE = 'POSITIVE';
    const PASSIVE = 'PASSIVE';
    const NEGATIVE = 'NEGATIVE';
    const NEUTRAL = 'NEUTRAL';

    public function __construct(RedisClient $redis)
    {
        $this->redis = $redis;
    }

    public function getPayload()
    {
        return new Payload();
    }

    protected function isTaggedFeedback($content)
    {
        return stripos($content, '#feedback') !== FALSE;
    }

    protected function createFeedback($body, $source, $tone = self::NEUTRAL)
    {
        $color = $this->colorForTone($tone);
        HipChatService::postMessage("<strong>From $source:</strong> $body", $color);

        GithubService::createIssue("Feedback from $source", $body);
    }

    private function colorForTone($tone) {
        switch ($tone) {
            case self::POSITIVE:
                return HipChatService::GREEN;
            case self::PASSIVE:
                return HipChatService::YELLOW;
            case self::NEGATIVE:
                return HipChatService::RED;
            default:
                return HipChatService::GRAY;
        }
    }
}
