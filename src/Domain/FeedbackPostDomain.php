<?php
namespace Wheniwork\Feedback\Domain;

use RuntimeException;
use Wheniwork\Feedback\Service\Authorizer;
use Wheniwork\Feedback\Service\GithubService;
use Wheniwork\Feedback\Service\HipChatService;

abstract class FeedbackPostDomain extends FeedbackDomain
{
    private $auth;

    public function __construct(
        HipChatService $hipchat,
        GithubService $github,
        Authorizer $auth
    ) {
        parent::__construct($hipchat, $github);
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
                $body = $this->getFeedbackHTML($input);
                $source = $this->getSourceName($input);

                if (!$debug) {
                    $this->createFeedback($body, $source);
                }

                $payload = $payload->withStatus($payload::OK);
                $payload = $payload->withOutput([
                    'new_feedback' => [
                        'body' => $body,
                        'source' => $source
                    ]
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
     * Gets the HTML-formatted feedback from the given input.
     * @param  array  $input The input for the domain.
     * @return string        The HTML-formatted feedback.
     */
    abstract protected function getFeedbackHTML(array $input);

    /**
     * Gets the source name for this domain.
     * @param  array  $input The input for the domain.
     * @return string        The name of this domain's feedback source.
     */
    abstract protected function getSourceName(array $input);

    /**
     * Gets the tone of feedback from the given input.
     * @param  array  $input The input for this domain.
     * @return string        The tone of feedback.
     */
    protected function getTone(array $input)
    {
        return self::NEUTRAL;
    }
}
