<?php
namespace Wheniwork\Feedback;

use Spark\Data\ArraySerializableInterface;
use Spark\Data\Traits\ImmutableValueObjectTrait;

class FeedbackRating implements ArraySerializableInterface
{
    use ImmutableValueObjectTrait;

    private $rating;
    private $max_rating;

    public function __construct($rating, $max_rating)
    {
        $this->rating = $rating;
        $this->max_rating = $max_rating;
    }
}
