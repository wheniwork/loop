<?php
namespace Wheniwork\Feedback\Domain;

abstract class FeedbackGetDomain extends FeedbackDomain
{
    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        try {
            if (empty($this->getRedisValue())) {
                $this->initRedis();
            }

            $feedbackItems = $this->getFeedbackItems($this->getRedisValue());

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

    abstract protected function getSourceName();

    protected function getOutputKeyName()
    {
        return "new_feedback_items";
    }

    abstract protected function getFeedbackItems();

    protected function initRedis()
    {
        $this->setRedisValue(1);
    }

    abstract protected function getValueForRedis($feedbackItem);

    abstract protected function getFeedbackHTML($feedbackItem);

    protected function getTone($feedbackItem)
    {
        return self::NEUTRAL;
    }
}
