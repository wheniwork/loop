<?php
namespace Wheniwork\Feedback\Formatter;

use Wheniwork\Feedback\FeedbackItem;
use Wheniwork\Feedback\FeedbackRating;
use Wheniwork\Feedback\Service\HipChatService;

class HipChatFormatter
{
    /**
     * Formats a FeedbackItem for use in HipChat.
     * @param  FeedbackItem $feedbackItem The item to format.
     * @return string                     An HTML-formatted representation of the item.
     */
    public function format(FeedbackItem $feedbackItem)
    {
        $output = '';

        $neededHeader = false;
        if ($feedbackItem->source) {
            $neededHeader = true;
            $output .= "<strong>From $feedbackItem->source:</strong> ";
        }
        if ($feedbackItem->title) {
            $neededHeader = true;
            $output .= "<strong>$feedbackItem->title</strong> ";
        }
        if ($feedbackItem->rating) {
            $neededHeader = true;
            $rating = $feedbackItem->rating;
            $output .= "<strong>($rating->rating/$rating->max_rating)</strong> ";
        }
        
        if ($neededHeader) {
            $output .= '<br>';
        }

        $output .= $feedbackItem->body;

        if ($feedbackItem->sender) {
            $output .= " <em>(From $feedbackItem->sender)</em>";
        }
        if ($feedbackItem->link) {
            $output .= "<br><br><a href=\"$feedbackItem->link\">$feedbackItem->link</a>";
        }

        return $output;
    }

    /**
     * Gets the HipChat color for a given feedback item.
     * @param  FeedbackItem $feedbackItem The feedback item to process.
     * @return string                     The appropriate HipChat color.
     */
    public function getColor(FeedbackItem $feedbackItem)
    {
        $tone = $feedbackItem->tone;
        switch ($tone) {
            case FeedbackItem::POSITIVE:
                return HipChatService::GREEN;
            case FeedbackItem::PASSIVE:
                return HipChatService::YELLOW;
            case FeedbackItem::NEGATIVE:
                return HipChatService::RED;
            default:
                return HipChatService::GRAY;
        }
    }
}
