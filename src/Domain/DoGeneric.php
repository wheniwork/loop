<?php
namespace Wheniwork\Feedback\Domain;

class DoGeneric extends FeedbackDomain
{
    public function __invoke(array $input)
    {
        $payload = $this->getPayload();

        if (!empty($input['body'])) {
            $this->createFeedback($input['body']);

            $payload->setStatus($payload::SUCCESS);
            $payload->setOutput(['new_feedback' => ['body' => $input['body']]]);
        } else {
            $payload->setStatus($payload::ERROR);
            $payload->setOutput(['error' => "Missing required field 'body'"]);
        }

        return $payload;
    }
}
