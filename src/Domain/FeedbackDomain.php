<?php
namespace Wheniwork\Feedback\Domain;

use Spark\Adr\DomainInterface;
use Spark\Payload;
use Wheniwork\Feedback\Service\GithubService;
use Wheniwork\Feedback\Service\HipChatService;

abstract class FeedbackDomain implements DomainInterface
{
    const POSITIVE = 'POSITIVE';
    const PASSIVE = 'PASSIVE';
    const NEGATIVE = 'NEGATIVE';
    const NEUTRAL = 'NEUTRAL';

    private $hipchat;
    private $github;

    public function __construct(
        HipChatService $hipchat,
        GithubService $github
    ) {
        $this->hipchat = $hipchat;
        $this->github = $github;
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
     * Creates a new feedback item, posts it to HipChat, and saves it.
     *
     * @param string $body      The content of the feedback item.
     * @param string $source    The name of the feedback item's source.
     * @param string $tone      The "tone" of the feedback, i.e. positive, passive, negative, or neutral.
     */
    protected function createFeedback($body, $source, $tone = self::NEUTRAL)
    {
        $color = $this->colorForTone($tone);
        $this->hipchat->postMessage("<strong>From $source:</strong> $body", $color);

        $this->github->createIssue("Feedback from $source", $body);
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
