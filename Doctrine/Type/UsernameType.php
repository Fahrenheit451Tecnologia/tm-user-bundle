<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 30/01/17
 * Time: 10:44 AM
 */

namespace TM\UserBundle\Doctrine\Type;

use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use TM\UserBundle\Exception\UsernameException;
use TM\UserBundle\Model\ValueObject\AbstractUsername;
use TM\UserBundle\Model\ValueObject\AbstractUsernameInterface;


class UsernameType extends Type
{
    /**
     * @var string
     */
    const NAME = 'username_type';

    /**
     * {@inheritdoc}
     *
     * @param array                                     $fieldDeclaration
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        if (!$platform->hasDoctrineTypeMappingFor('jsonb')) {
            return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
        }

        return 'JSONB';
    }

    /**
     * {@inheritdoc}
     *
     * @param string|null                               $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof AbstractUsernameInterface) {
            return $value;
        }

        try {
            $details = AbstractUsername::fromJson($value);
        } catch (UsernameException $e) {
            throw ConversionException::conversionFailed($value, self::NAME);
        } catch (\InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, self::NAME);
        }

        return $details;
    }

    /**
     * {@inheritdoc}
     *
     * @param AbstractUsernameInterface|null             $value
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof AbstractUsernameInterface) {
            return $value->toJson();
        }

        throw ConversionException::conversionFailed($value, self::NAME);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return boolean
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}