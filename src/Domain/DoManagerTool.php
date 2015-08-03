<?php
namespace Wheniwork\Feedback\Domain;

class DoManagerTool extends FeedbackPostDomain
{
    protected function getRequiredFields()
    {
        return ['body', 'account_id', 'account_name'];
    }

    protected function getFeedbackHTML(array $input)
    {
        $body = $input['body'];
        $id = $input['account_id'];
        $name = $input['account_name'];
        return "$body <i>(From $name, #$id)</i>";
    }

    protected function getSourceName(array $input)
    {
        return "the Manager Tool";
    }
}
