<?php
namespace Wheniwork\Feedback\Domain;

use RuntimeException;
use Wheniwork\Feedback\Service\Authorizer;
use Wheniwork\Feedback\Service\GithubService;
use Wheniwork\Feedback\Service\HipChatService;

class DoZendesk extends FeedbackDomain
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
            if (empty($input['link'])) {
                throw new RuntimeException("Missing required field 'link'");
            }

            $body = $input['body'];
            $body = preg_replace("/-{46}.*?(AM|PM)\s+/s", "", $body);
            $body = preg_replace("/--\s+\[.*\].*/s", "", $body);
            if (strlen($body) > 400) {
                $body = preg_replace("/\s+?(\S+)?$/", "", substr($body, 0, 401)) . "...";
            }

            $link = "https://" . $input['link'];

            $body_content = preg_replace("/<br><br><a href.*?<\/a>/", "", $body);
            if (!empty(trim($body_content))) {
                $this->createFeedback($body, "Zendesk");
            }

            $payload->setStatus($payload::SUCCESS);
            $payload->setOutput([
                'new_feedback' => [
                    'body' => $body,
                    'source' => "Zendesk"
                ]
            ]);
        } catch (Exception $e) {
            $payload->setStatus($payload::ERROR);
            $payload->setOutput($e);
        }

        return $payload;
    }
}
