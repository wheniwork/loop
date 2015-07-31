<?php
namespace Wheniwork\Feedback\Domain;

use RuntimeException;
use Wheniwork\Feedback\Service\Authorizer;
use Wheniwork\Feedback\Service\GithubService;
use Wheniwork\Feedback\Service\HipChatService;

class DoGeneric extends FeedbackDomain
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

        try {
            if (! $this->auth->checkInput($input)) {
                throw new RuntimeException("Unable to authenticate.");
            }

            if (empty($input['body'])) {
                throw new RuntimeException("Missing required field 'body'");
            }
            if (empty($input['source'])) {
                throw new RuntimeException("Missing required field 'source'");
            }

            $tone = self::NEUTRAL;
            if (!empty($input['tone'])) {
                $tone = strtoupper($input['tone']);
            }

            $this->createFeedback($input['body'], $input['source'], $tone);

            $payload->setStatus($payload::SUCCESS);
            $payload->setOutput([
                'new_feedback' => [
                    'body' => $input['body'],
                    'source' => $input['source']
                ]
            ]);
        } catch (Exception $e) {
            $payload->setStatus($payload::ERROR);
            $payload->setOutput($e);
        }

        return $payload;
    }
}
