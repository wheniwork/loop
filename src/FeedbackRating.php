<?php
namespace Wheniwork\Feedback;

class FeedbackRating
{
    private $rating;
    private $maxRating;

    public function __construct($rating, $maxRating) {
        $this->rating = $rating;
        $this->maxRating = $maxRating;
    }
}
