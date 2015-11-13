<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;
use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\Service\DatabaseService;
use Wheniwork\Feedback\Service\HipChatService;

abstract class FeedbackGetDomain extends FeedbackDomain
{
    private $redis;
    
    public function __construct(
        HipChatService $hipchat,
        DatabaseService $database,
        RedisClient $redis
    ) {
        parent::__construct($hipchat, $database);
        $this->redis = $redis;
    }

    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        $debug = $this->isDebug($input);

        try {
            if (empty($this->getRedisValue())) {
                $this->initRedis();
            }

            $rawFeedbacks = $this->getRawFeedbacks();

            if (count($rawFeedbacks) > 0 && !$debug) {
                $this->setRedisValue($this->getValueForRedis(reset($rawFeedbacks)));
            }

            $output = [$this->getOutputKeyName() => []];
            foreach ($rawFeedbacks as $rawFeedback) {
                $feedbackItem = $this->createFeedbackItem($rawFeedback);
                if (!$debug) {
                    $this->processFeedback($feedbackItem);
                }
                array_push($output[$this->getOutputKeyName()], $feedbackItem->toArray());
            }

            $payload = $payload->withStatus($payload::OK);
            $payload = $payload->withOutput($output);
        } catch (Exception $e) {
            $payload = $payload->withStatus($payload::ERROR);
            $payload = $payload->withOutput($e);
        }
        
        return $payload;
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
    protected function getRedisValue()
    {
        return $this->redis->get($this->getRedisKey());
    }

    /**
     * Sets the Redis cache for this domain.
     *
     * @param int $value    The value to save in Redis.
     */
    protected function setRedisValue($value)
    {
        $this->redis->set($this->getRedisKey(), $value);
    }

    /**
     * Gets the name of the key used in a successful output.
     *
     * @return string   The name of the key.
     */
    protected function getOutputKeyName()
    {
        return "new_feedback_items";
    }

    /**
     * Gets raw feedback responses from the domain's source.
     * Each of the raw feedbacks returned must be able to be
     * passed to createFeedbackItem().
     * 
     * @return array    The new feedback responses.
     */
    abstract protected function getRawFeedbacks();

    /**
     * Creates a new FeedbackItem from an instance of raw
     * feedback.
     *
     * @return FeedbackItem     The new feedback item.
     */
    abstract protected function createFeedbackItem($rawFeedback);

    /**
     * Initializes this domain's Redis cache to an appropriate
     * default value.
     */
    protected function initRedis()
    {
        $this->setRedisValue(1);
    }

    /**
     * Given a feedback item, gets the value to cache in Redis.
     *
     * @param FeedbackItem $feedbackItem   The item to get a value from.
     * 
     * @return int                  The property of the given item to cache in Redis.
     */
    abstract protected function getValueForRedis($feedbackItem);
}
