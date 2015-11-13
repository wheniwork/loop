<?php
namespace Wheniwork\Feedback\Domain;

use RuntimeException;
use Wheniwork\Feedback\Service\Authorizer;
use Wheniwork\Feedback\Service\DatabaseService;
use Wheniwork\Feedback\Service\HipChatService;

abstract class FeedbackPostDomain extends FeedbackDomain
{
    private $auth;

    public function __construct(
        HipChatService $hipchat,
        DatabaseService $database,
        Authorizer $auth
    ) {
        parent::__construct($hipchat, $database);
        $this->auth = $auth;
    }

    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        $debug = $this->isDebug($input);

        try {
            $this->auth->ensure($input);

            $missing = array_diff($this->getRequiredFields(), array_keys($input));
            if (!empty($missing)) {
                throw new RuntimeException(
                    'Missing required fields: ' .
                    implode(', ', $missing)
                );
            }

            if ($this->isValid($input)) {
                $feedbackItem = $this->createFeedbackItem($input);

                if (!$debug) {
                    $this->processFeedback($feedbackItem);
                }

                $payload = $payload->withStatus($payload::OK);
                $payload = $payload->withOutput([
                    'new_feedback' => $feedbackItem->toArray()
                ]);
            } else {
                $payload = $payload->withStatus($payload::INVALID);
                $payload = $payload->withOutput([
                    'error' => 'Input was not valid.'
                ]);
            }
        } catch (Exception $e) {
            $payload = $payload->withStatus($payload::ERROR);
            $payload = $payload->withOutput($e);
        }

        return $payload;
    }

    /**
     * Gets the input fields that are required by this domain.
     * @return array The required fields, as an array of strings.
     */
    abstract protected function getRequiredFields();

    /**
     * Checks whether the given input is valid.
     * @param  array   $input The input for the domain.
     * @return boolean        Whether the input is valid.
     */
    protected function isValid(array $input)
    {
        return true;
    }

    /**
     * Creates a FeedbackItem from the given input.
     * @param  array  $input The input for the domain.
     * @return FeedbackItem  The processed feedback.
     */
    abstract protected function createFeedbackItem(array $input);
}
