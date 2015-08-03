<?php
namespace Wheniwork\Feedback\Domain;

class DoZendesk extends FeedbackPostDomain
{
    protected function getRequiredFields()
    {
        return ['body', 'link'];
    }

    protected function getFeedbackHTML(array $input)
    {
        $body = $input['body'];
        $body = preg_replace("/-{46}.*?(AM|PM)\s+/s", "", $body);
        $body = preg_replace("/--\s+\[.*\].*/s", "", $body);
        if (strlen($body) > 400) {
            $body = preg_replace("/\s+?(\S+)?$/", "", substr($body, 0, 401)) . "...";
        }

        $link = "https://" . $input['link'];

        return "$body<br><br><a href=\"$link\">$link</a>";
    }

    protected function getSourceName(array $input)
    {
        return "Zendesk";
    }
}
