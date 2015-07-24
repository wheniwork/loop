<?php
namespace Wheniwork\Feedback\Domain;

use RuntimeException;

class DoGeneric extends FeedbackDomain
{
    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        try {
            if (empty($input['key'])) {
                throw new RuntimeException("You must provide a key with your request.");
            } else if ($input['key'] != $_ENV['GENERIC_KEY']) {
                throw new RuntimeException("The provided authentication key was invalid.");
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
