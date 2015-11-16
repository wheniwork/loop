<?php
namespace Wheniwork\Feedback\Domain;

use Spark\Payload;
use Spark\Adr\DomainInterface;
use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\Formatter\HipChatFormatter;
use Wheniwork\Feedback\Service\DatabaseService;
use Wheniwork\Feedback\Service\HipChatService;

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
        $this->hipchat->postFeedback($feedbackItem);
        $this->database->addFeedbackItem($feedbackItem);
    }
}
