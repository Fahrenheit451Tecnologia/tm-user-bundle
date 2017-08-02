<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 30/01/17
 * Time: 09:52 AM
 */

namespace TM\UserBundle\Model\ValueObject;

use Assert\Assertion;

class NameField extends AbstractUsername
{
    /**
     * @var string
     */
    private $name;

    /**
     * NameField constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        Assertion::string($name);

        $this->name = $name;
    }

    /**
     *
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            '_type' => self::TYPE_NAME,
            'name'  => $this->name
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

        Assertion::keyExists($data, 'name');

        return new static(
            $data['name']
        );
    }
}