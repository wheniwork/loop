<?php
namespace Wheniwork\Feedback;

use Equip\Data\EntityInterface;
use Equip\Data\Traits\EntityTrait;

class FeedbackItem
{
    use EntityTrait;

    const POSITIVE = 'positive';
    const PASSIVE = 'passive';
    const NEGATIVE = 'negative';
    const NEUTRAL = 'neutral';

    private $body;
    private $source;
    private $link;
    private $title;
    private $rating;
    private $sender;
    private $tone;

    private function types()
    {
        return [
            'body' => 'string',
            'source' => 'string',
            'link' => 'string',
            'title' => 'string',
            'sender' => 'string',
            'tone' => 'string'
        ];
    }

    private function expects()
    {
        return [
            'rating' => FeedbackRating::class
        ];
    }

    private function validate()
    {
        if (!$this->body) {
            throw new \DomainException('All feedback items must have a body');
        }
        if ($this->link && !filter_var($this->link, FILTER_VALIDATE_URL)) {
            throw new \DomainException('\'link\' must be a valid URL'); 
        }
    }
}
