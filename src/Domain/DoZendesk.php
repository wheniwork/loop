<?php
namespace Wheniwork\Feedback\Domain;

use RuntimeException;

class DoZendesk extends FeedbackDomain
{
    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        try {
            if (empty($input['key'])) {
                throw new RuntimeException("You must provide a key with your request.");
            } else if ($input['key'] != $_ENV['POST_KEY']) {
                throw new RuntimeException("The provided authentication key was invalid.");
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
