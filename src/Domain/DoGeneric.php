<?php
namespace Wheniwork\Feedback\Domain;

use RuntimeException;

class DoGeneric extends FeedbackDomain
{
    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        try {
            if (empty($input['body'])) {
                throw new RuntimeException("Missing required field 'body'");
            }
            if (empty($input['source'])) {
                throw new RuntimeException("Missing required field 'source'");
            }

            $this->createFeedback($input['body'], $input['source']);

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
