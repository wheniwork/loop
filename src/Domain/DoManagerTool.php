<?php
namespace Wheniwork\Feedback\Domain;

use RuntimeException;

class DoManagerTool extends FeedbackDomain
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
            if (empty($input['account_id'])) {
                throw new RuntimeException("Missing required field 'account_id'");
            }
            if (!isset($input['account_name'])) {
                throw new RuntimeException("Missing required field 'account_name'");
            }

            $tone = self::NEUTRAL;
            if (!empty($input['tone'])) {
                $tone = strtoupper($input['tone']);
            }

            $body = $input['body'];
            $id = $input['account_id'];
            $name = $input['account_name'];
            $this->createFeedback("$body <i>(From $name, #$id)</i>", $input['source'], $tone);

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
