<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\FeedbackItem;

class DoZendesk extends FeedbackPostDomain
{
    protected function getRequiredFields()
    {
        return ['body', 'link'];
    }

    protected function createFeedbackItem(array $input)
    {
        $body = $input['body'];
        $body = preg_replace('/-{46}.*?(AM|PM)\s+/s', '', $body);
        $body = preg_replace('/--\s+\[.*\].*/s', '', $body);
        if (strlen($body) > 400) {
            $body = preg_replace('/\s+?(\S+)?$/', '', substr($body, 0, 401)) . '...';
        }

        $link = 'https://' . $input['link'];

        return (new FeedbackItem)->withData([
            'body' => $body,
            'source' => 'Zendesk',
            'link' => $link
        ]);
    }
}
