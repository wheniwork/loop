<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\FeedbackRating;

class DoGeneric extends FeedbackPostDomain
{
    protected function getRequiredFields()
    {
        return ['body', 'source'];
    }

    protected function isValid(array $input)
    {
        if (
            !empty($input['tone']) &&
            $input['tone'] != FeedbackItem::POSITIVE &&
            $input['tone'] != FeedbackItem::PASSIVE &&
            $input['tone'] != FeedbackItem::NEGATIVE &&
            $input['tone'] != FeedbackItem::NEUTRAL
        ) {
            return false;
        }

        return parent::isValid($input);
    }

    protected function createFeedbackItem(array $input)
    {
        $item = (new FeedbackItem)->withData([
            'body' => $input['body'],
            'source' => $input['source'],
        ]);

        if (!empty($input['title'])) {
            $item = $item->withData([
                'title' => $input['title']
            ]);
        }

        if (!empty($input['tone'])) {
            $item = $item->withData([
                'tone' => strtolower($input['tone'])
            ]);
        }

        return $item;
    }
}
