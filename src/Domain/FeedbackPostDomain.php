<?php
namespace Wheniwork\Feedback\Domain;

use Equip\Adr\PayloadInterface;
use Wheniwork\Feedback\Exception\AuthorizationException;
use Wheniwork\Feedback\FeedbackItem;
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
        $isDebug = $this->isDebug($input);

        $payload = $this->getPayload();
        
        // Ensure authentication
        try {
            $this->auth->ensure($input);
        } catch (AuthorizationException $e) {
            return $payload
                ->withStatus(PayloadInterface::STATUS_BAD_REQUEST)
                ->withOutput([
                    'error' => $e->getMessage()
                ]);
        }

        // Check for missing fields
        $missingFields = array_diff($this->getRequiredFields(), array_keys($input));
        if (!empty($missingFields)) {
            $missingMessage = 'Missing required fields: ' .
                implode(', ', $missingFields);
            return $payload
                ->withStatus(PayloadInterface::STATUS_BAD_REQUEST)
                ->withOutput([
                    'error' => $missingMessage
                ]);
        }

        // Check if input is valid
        if (!$this->isValid($input)) {
            return $payload->withStatus(PayloadInterface::STATUS_BAD_REQUEST)
                ->withOutput([
                    'error' => 'Input was not valid.'
                ]);
        }
        
        $feedbackItem = $this->createFeedbackItem($input);

        if (!$isDebug) {
            $this->processFeedback($feedbackItem);
        }

        return $payload
            ->withStatus(PayloadInterface::STATUS_OK)
            ->withOutput([
                'new_feedback' => $feedbackItem->toArray()
            ]);
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
