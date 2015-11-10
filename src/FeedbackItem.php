<?php
namespace Wheniwork\Feedback;

use Spark\Data\EntityInterface;
use Spark\Data\Traits\EntityTrait;

class FeedbackItem
{
    use EntityTrait {
        apply as private applyData;
    }

    const POSITIVE = 'positive';
    const PASSIVE = 'passive';
    const NEGATIVE = 'negative';
    const NEUTRAL = 'neutral';

    private $body;
    private $source;
    private $title;
    private $rating;
    private $sender;
    private $tone;

    private function types()
    {
        return [
            'body' => 'string',
            'source' => 'string',
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

    private function apply(array $data)
    {
        if (!$this->body && empty($data['body'])) {
            throw new Spark\DomainException('All feedback items must have a body');
        }
        
        return $this->applyData($data);
    }
}
