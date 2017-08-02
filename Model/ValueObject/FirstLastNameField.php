<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 30/01/17
 * Time: 10:16 AM
 */

namespace TM\UserBundle\Model\ValueObject;

use Assert\Assertion;

class FirstLastNameField extends AbstractUsername
{
    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct(
        string $firstName,
        string $lastName
    )
    {
        Assertion::string($firstName);
        Assertion::string($lastName);

        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     *
     * Get First Name
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     *
     * Get Last Name
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            '_type' => self::TYPE_FIRST_LAST_NAME,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data): AbstractUsernameInterface
    {
        if(array_key_exists('_type', $data)){
            return parent::fromArray($data);
        }

        Assertion::keyExists($data, 'first_name');
        Assertion::keyExists($data, 'last_name');

        return new static(
            $data['first_name'],
            $data['last_name']
        );
    }
}