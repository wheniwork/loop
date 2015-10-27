<?php
namespace Wheniwork\Feedback\Domain;

class DoGeneric extends FeedbackPostDomain
{
    protected function getRequiredFields()
    {
        return ['body', 'source'];
    }

    protected function getFeedbackHTML(array $input)
    {
        return $input['body'];
    }

    protected function getSourceName(array $input)
    {
        return $input['source'];
    }

    protected function getTone(array $input)
    {
        $tone = self::NEUTRAL;
        if (!empty($input['tone'])) {
            $tone = strtoupper($input['tone']);
        }
        return $tone;
    }
}
