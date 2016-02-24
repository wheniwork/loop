<?php
namespace Wheniwork\Feedback\Service;

use Wheniwork\Feedback\Exception\AuthorizationException;

class Authorizer
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function ensure(array $input)
    {
        if (empty($input['key'])) {
            throw new AuthorizationException('You must provide a key with your request.');
        } elseif ($input['key'] != $this->key) {
            throw new AuthorizationException('The provided authentication key was invalid.');
        }
    }
}
