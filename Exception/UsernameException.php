<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 30/01/17
 * Time: 10:10 AM
 */

namespace TM\UserBundle\Exception;


use TM\UserBundle\Model\UserInterface;
use TM\UserBundle\Model\ValueObject\AbstractUsernameInterface;

class UsernameException extends \Exception
{
    /**
     * @param string $json
     * @return UsernameException
     */
    public static function invalidJson(string $json) : UsernameException
    {
        return new self(sprintf(
            'JSON string is invalid. Provided: %s',
            $json
        ));
    }

    /**
     * @param UserInterface $user
     * @return UsernameException
     */
    public static function noTypeSet(UserInterface $user) : UsernameException
    {
        return new self(sprintf(
            'Contact detail with id "%s" has no type set',
            (string) $user->getId()
        ));
    }

    /**
     * @param array $data
     * @return UsernameException
     */
    public static function noTypeInSerializedData(array $data) : UsernameException
    {
        return new self(sprintf(
            'Serialized array has no "_type" key. Available keys: "%s"',
            implode(", ", array_keys($data))
        ));
    }

    /**
     * @param string $type
     * @return UsernameException
     */
    public static function invalidDetailType(string $type) : UsernameException
    {
        return new self(sprintf(
            'Detail type "%s" is not valid. Available types: "%s"',
            $type,
            implode(", ", array_keys(AbstractUsernameInterface::CLASS_MAP))
        ));
    }
}