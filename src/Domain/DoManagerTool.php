<?php
namespace Wheniwork\Feedback\Domain;

use Wheniwork\Feedback\FeedbackItem;

class DoManagerTool extends FeedbackPostDomain
{
    protected function getRequiredFields()
    {
        return ['body', 'account_id', 'account_name'];
    }

    protected function createFeedbackItem(array $input)
    {
        $id = $input['account_id'];
        $name = $input['account_name'];

        return (new FeedbackItem)->withData([
            'body' => $input['body'],
            'source' => 'the Manager Tool',
            'sender' => "$name, #$id"
        ]);
    }
}
