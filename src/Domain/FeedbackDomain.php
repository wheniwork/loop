<?php
namespace Wheniwork\Feedback\Domain;

use Spark\Adr\DomainInterface;
use Spark\Payload;
use Wheniwork\Feedback\Service\DatabaseService;
use Wheniwork\Feedback\Service\HipChatService;
use Wheniwork\Feedback\FeedbackItem;

abstract class FeedbackDomain implements DomainInterface
{
    private $hipchat;
    private $database;

    public function __construct(
        HipChatService $hipchat,
        DatabaseService $database
    ) {
        $this->hipchat = $hipchat;
        $this->database = $database;
    }

    public function getPayload()
    {
        return new Payload();
    }

    protected function isDebug(array $input)
    {
        return isset($input['debug']) && filter_var($input['debug'], FILTER_VALIDATE_BOOLEAN);
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
     * Posts a feedback item to HipChat, and saves it to the database.
     *
     * @param FeedbackItem $feedbackItem    The item to process.
     */
    protected function processFeedback(FeedbackItem $feedbackItem) {
        $color = $this->colorForTone($feedbackItem->tone);
        $this->hipchat->postMessage("<strong>From $feedbackItem->source:</strong> $feedbackItem->body", $color);

        $this->database->addFeedbackItem($feedbackItem);
    }

    /**
     * Gets the HipChat color for a given feedback tone.
     *
     * @param string $tone  The tone of the feedback.
     */
    private function colorForTone($tone) {
        switch ($tone) {
            case FeedbackItem::POSITIVE:
                return HipChatService::GREEN;
            case FeedbackItem::PASSIVE:
                return HipChatService::YELLOW;
            case FeedbackItem::NEGATIVE:
                return HipChatService::RED;
            default:
                return HipChatService::GRAY;
        }
    }
}
