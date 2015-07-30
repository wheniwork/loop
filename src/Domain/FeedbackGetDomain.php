<?php
namespace Wheniwork\Feedback\Domain;

use Predis\Client as RedisClient;

abstract class FeedbackGetDomain extends FeedbackDomain
{
    protected $redis;

    public function __construct(RedisClient $redis)
    {
        $this->redis = $redis;
    }

    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        try {
            if (empty($this->getRedisValue())) {
                $this->initRedis();
            }

            $feedbackItems = $this->getFeedbackItems();

            if (count($feedbackItems) > 0) {
                $this->setRedisValue($this->getValueForRedis(reset($feedbackItems)));
            }

            $output = [$this->getOutputKeyName() => []];
            foreach ($feedbackItems as $feedbackItem) {
                $feedback_html = $this->getFeedbackHTML($feedbackItem);
                $source = $this->getSourceName();
                $tone = $this->getTone($feedbackItem);

                $this->createFeedback($feedback_html, $source, $tone);
                array_push($output[$this->getOutputKeyName()], $feedbackItem);
            }

            $payload->setStatus($payload::SUCCESS);
            $payload->setOutput($output);
        } catch (Exception $e) {
            $payload->setStatus($payload::ERROR);
            $payload->setOutput($e);
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
     * Gets the name of this domain's feedback source. Used in the
     * context of the phrase "Feedback from [source]", so keep that
     * in mind when implementing this.
     *
     * @return string   The name of the feedback source.
     */
    abstract protected function getSourceName();

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
     * Gets new feedback items from this domain's source. ALL
     * returned items should be able to be processed as new
     * feedback.
     *
     * @return array    The new feedback items.
     */
    abstract protected function getFeedbackItems();

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
     * @param mixed $feedbackItem   The item to get a value from.
     * 
     * @return int                  The property of the given item to cache in Redis.
     */
    abstract protected function getValueForRedis($feedbackItem);

    /**
     * Given a feedback item, gets the HTML-formatted message to
     * store and to send to HipChat.
     *
     * @param mixed $feedbackItem   The item to get a value from.
     *
     * @return string               The HTML-formatted version of the feedback.
     */
    abstract protected function getFeedbackHTML($feedbackItem);

    /**
     * Given a feedback item, gets the tone of the feedback. By
     * default this is neutral, but domains can override this.
     *
     * @param mixed $feedbackItem   The item to get a value from.
     *
     * @return string               The tone of the feedback.
     */
    protected function getTone($feedbackItem)
    {
        return self::NEUTRAL;
    }
}
