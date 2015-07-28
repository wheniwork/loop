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

            $body = $input['body'];
            $body = preg_replace("/-{46}.*?(AM|PM)\s+/s", "", $body);
            $body = preg_replace("/--\s+?\[.*?\].*?<a href/s", "<br><br><a href", $body);

            $body_content = preg_replace("/<br><br><a href.*?<\/a>/", "", $body);
            print_r($body_content);
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
