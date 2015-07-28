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

    /**
     * Checks whether the given content is tagged as feedback.
     *
     * @param string $content   The content to check.
     */
    protected function isTaggedFeedback($content)
    {
        return stripos($content, '#feedback') !== FALSE;
    }

    /**
     * Creates a new feedback item, posts it to HipChat, and saves it.
     *
     * @param string $body      The content of the feedback item.
     * @param string $source    The name of the feedback item's source.
     * @param string $tone      The "tone" of the feedback, i.e. positive, passive, negative, or neutral.
     */
    protected function createFeedback($body, $source, $tone = self::NEUTRAL)
    {
        $color = $this->colorForTone($tone);
        HipChatService::postMessage("<strong>From $source:</strong> $body", $color);

        GithubService::createIssue("Feedback from $source", $body);
    }

    /**
     * Gets the key for this domain's cache in Redis.
     *
     * @return string   The key to use in Redis.
     */
    abstract protected function getRedisKey();

    /**
     * Gets the cached value for this domain from Redis.
     */
    protected function getRedisValue() {
        return $this->redis->get($this->getRedisKey());
    }

    /**
     * Sets the Redis cache for this domain.
     *
     * @param int $value    The value to save in Redis.
     */
    protected function setRedisValue($value) {
        $this->redis->set($this->getRedisKey(), $value);
    }

    /**
     * Gets the HipChat color for a given feedback tone.
     *
     * @param string $tone  The tone of the feedback.
     */
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
