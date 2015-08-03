<?php
namespace Wheniwork\Feedback\Domain;

class DoZendesk extends FeedbackPostDomain
{
    protected function getRequiredFields()
    {
        return ['body'];
    }

    protected function isValid(array $input)
    {
        $body_content = preg_replace("/<br><br><a href.*?<\/a>/", "", $this->getFeedbackHTML($input));
        return !empty(trim($body_content));
    }

    protected function getFeedbackHTML(array $input)
    {
        $body = $input['body'];
        $body = preg_replace("/-{46}.*?(AM|PM)\s+/s", "", $body);
        $body = preg_replace("/--\s+?\[.*?\].*?<a href/s", "<br><br><a href", $body);
        return $body;
    }

    protected function getSourceName(array $input)
    {
        return "Zendesk";
    }
}
