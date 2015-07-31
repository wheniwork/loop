<?php
namespace Wheniwork\Feedback\Service;

class Authorizer
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function checkInput(array $input)
    {
        return !empty($input['key']) && $input['key'] == $this->key;
    }
}
