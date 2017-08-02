<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 30/01/17
 * Time: 09:27 AM
 */

namespace TM\UserBundle\Model\ValueObject;

use TM\UserBundle\Exception\UsernameException;

abstract class AbstractUsername implements AbstractUsernameInterface
{
    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode(static::toArray());
    }

    /**
     * @param string $json
     * @return AbstractUsernameInterface
     * @throws UsernameException
     */
    public static function fromJson(string $json) : AbstractUsernameInterface
    {
        if (null === $array = json_decode($json, true)) {
            throw UsernameException::invalidJson($json);
        }

        return static::fromArray($array);
    }

    /**
     * @param array $data
     * @return AbstractUsernameInterface
     * @throws UsernameException
     */
    public static function fromArray(array $data) : AbstractUsernameInterface
    {
        if (!isset($data['_type'])) {
            throw UsernameException::noTypeInSerializedData($data);
        }

        if (!array_key_exists($data['_type'], self::CLASS_MAP)) {
            throw UsernameException::invalidDetailType($data['_type']);
        }

        /** @var AbstractUsernameInterface $detailsClass */
        $detailsClass = self::CLASS_MAP[$data['_type']];

        unset($data['_type']);

        return $detailsClass::fromArray($data);
    }
}