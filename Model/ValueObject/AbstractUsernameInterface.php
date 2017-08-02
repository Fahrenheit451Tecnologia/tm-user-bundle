<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 30/01/17
 * Time: 09:21 AM
 */

namespace TM\UserBundle\Model\ValueObject;


interface AbstractUsernameInterface
{
    const TYPE_NAME             = 'name';
    const TYPE_FIRST_LAST_NAME  = 'first_last_name';

    const CLASS_MAP         = [
        self::TYPE_NAME => NameField::class
    ];

    const TYPES             = [
        self::TYPE_NAME,
    ];

    /**
     * @return string
     */
    public function toJson();

    /**
     * @param string $json
     * @return AbstractUsernameInterface
     */
    public static function fromJson(string $json) : AbstractUsernameInterface;

    /**
     * @return array
     */
    public function toArray();

    /**
     * @param array $data
     * @return AbstractUsernameInterface
     */
    public static function fromArray(array $data) : AbstractUsernameInterface;
}