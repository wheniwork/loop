<?php
namespace Wheniwork\Feedback;

use Spark\Data\ArraySerializableInterface;

class FeedbackRating implements ArraySerializableInterface
{
    private $rating;
    private $max_rating;

    public function __construct($rating, $max_rating) {
        $this->rating = $rating;
        $this->max_rating = $max_rating;
    }

    public function __get($key)
    {
        return $this->$key;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}
