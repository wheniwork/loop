<?php

namespace FeedbackTests;

use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\FeedbackRating;

class FeedbackTestBase extends \PHPUnit_Framework_TestCase
{
    const ITEM_BODY = "test_body";
    const ITEM_SOURCE = "test_source";
    const ITEM_LINK = "http://www.example.com/testlink";
    const ITEM_TITLE = "test_title";
    const ITEM_RATING = 5;
    const ITEM_MAX_RATING = 5;
    const ITEM_SENDER = "test_sender";
    const ITEM_TONE = "test_tone";

    public function getFeedbackItem()
    {
        return (new FeedbackItem)->withData([
            'body' => self::ITEM_BODY,
            'source' => self::ITEM_SOURCE,
            'link' => self::ITEM_LINK,
            'title' => self::ITEM_TITLE,
            'rating' => new FeedbackRating(
                self::ITEM_RATING,
                self::ITEM_MAX_RATING
            ),
            'sender' => self::ITEM_SENDER,
            'tone' => self::ITEM_TONE,
        ]);
    }
}
