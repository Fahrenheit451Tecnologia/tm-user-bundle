<?php

namespace TM\UserBundle\Exception;

class UserException extends \Exception
{
    /**
     * @param string $username
     * @return UserException
     */
    public static function usernameNotUnique(string $username) : UserException
    {
        return new self(sprintf(
            'Username "%s" is already in use',
            $username
        ));
    }
}