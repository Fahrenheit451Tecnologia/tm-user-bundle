<?php

namespace TM\UserBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use TM\RbacBundle\Model\PermissionInterface;
use TM\ResourceBundle\Loggable\LoggableTrait;
use TM\ResourceBundle\Model\Traits\HasClientGeneratedIdTrait;
use Symfony\Component\Validator\Constraints as Validate;
use TM\ResourceBundle\Model\ValueObject\Locale;
use TM\ResourceBundle\Util\Canonicalizer;
use TM\ResourceBundle\Uuid\UuidInterface;
use TM\JsonApiBundle\Serializer\Configuration\Annotation as JsonApi;
use TM\UserBundle\Model\ValueObject\AbstractUsernameInterface;

/**
 * @Serializer\ExclusionPolicy("ALL")
 */
abstract class User implements UserInterface
{
    use HasClientGeneratedIdTrait,
        LoggableTrait;

    /**
     * @var UuidInterface
     */
    protected $id;

    /**
     * Name of the type of the valueObject username_type, see \UserBundle\Model\ValueObject\AbstractUsernameInterface
     *
     * @ORM\Column(name="name_type_name", type="string", nullable=true)
     *
     * @var string
     */
    protected $nameTypeName;

    /**
     * ValueObject username_type
     *
     * @ORM\Column(name="name_type", type="username_type", length=255, nullable=true)
     * @Serializer\Expose
     * @Serializer\SerializedName("name-type")
     *
     * @var string
     */
    protected $nameType;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=255)
     * 
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(name="username_canonical", type="citext", length=255, unique=true)
     * @Serializer\Expose
     * @Serializer\SerializedName("username")
     *
     * @var string
     */
    protected $usernameCanonical;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Length(min=2, max=254)
     * @Assert\Email
     *
     * @var string
     */
    protected $email;

    /**
     * @ORM\Column(name="email_canonical", type="citext", length=255, unique=true)
     * @Serializer\Expose
     * @Serializer\SerializedName("email")
     *
     * @var string
     */
    protected $emailCanonical;

    /**
     * @ORM\Column(type="boolean")
     * @Serializer\Expose
     *
     * @var boolean
     */
    protected $enabled;

    /**
     * @ORM\Column(type="string")
     * 
     * The salt to use for hashing
     *
     * @var string
     */
    protected $salt;

    /**
     * @ORM\Column(type="string")
     * 
     * Encrypted password. Must be persisted.
     *
     * @var string
     */
    protected $password;

    /**
     * @Assert\Length(min=2, max=4096)
     *
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     * @Serializer\Expose
     * @Serializer\SerializedName("last-login")
     *
     * @var \DateTimeImmutable
     */
    protected $lastLogin;

    /**
     * @ORM\Column(name="confirmation_token", type="string", nullable=true)
     * 
     * Random string sent to the user email address in order to verify it
     *
     * @var string
     */
    protected $confirmationToken;

    /**
     * @ORM\Column(name="password_requested_at", type="datetime_immutable", nullable=true)
     * 
     * @var \DateTimeImmutable
     */
    protected $passwordRequestedAt;

    /**
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    protected $locked;

    /**
     * @ORM\Column(type="boolean")
     * 
     * @var boolean
     */
    protected $expired;

    /**
     * @ORM\Column(name="expires_at", type="datetime_immutable", nullable=true)
     * 
     * @var \DateTimeImmutable
     */
    protected $expiresAt;

    /**
     * @ORM\Column(name="credentials_expired", type="boolean")
     * 
     * @var boolean
     */
    protected $credentialsExpired;

    /**
     * @ORM\Column(name="credentials_expire_at", type="datetime_immutable", nullable=true)
     * 
     * @var \DateTimeImmutable
     */
    protected $credentialsExpireAt;

    /**
     * @ORM\Column(type="locale")
     * @Serializer\Expose
     * @Assert\NotBlank
     *
     * @var Locale
     */
    protected $locale;

    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->enabled = false;
        $this->locked = false;
        $this->expired = false;
        $this->credentialsExpired = false;
    }

    /**
     * Serializes the user.
     *
     * The serialized data have to contain the fields used during check for
     * changes and the id.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->nameType,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->expiresAt,
            $this->credentialsExpireAt,
            $this->email,
            $this->emailCanonical,
        ));
    }

    /**
     * Unserializes the user.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->password,
            $this->salt,
            $this->nameType,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->expiresAt,
            $this->credentialsExpireAt,
            $this->email,
            $this->emailCanonical
        ) = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getUsernameCanonical()
    {
        return $this->usernameCanonical;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * Gets the encrypted password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }


    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Gets the last login time.
     *
     * @return \DateTimeImmutable
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    public function isAccountNonExpired()
    {
        if (true === $this->expired) {
            return false;
        }

        if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    public function isCredentialsNonExpired()
    {
        if (true === $this->credentialsExpired) {
            return false;
        }

        if (null !== $this->credentialsExpireAt && $this->credentialsExpireAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    public function isCredentialsExpired()
    {
        return !$this->isCredentialsNonExpired();
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function isExpired()
    {
        return !$this->isAccountNonExpired();
    }

    public function isLocked()
    {
        return !$this->isAccountNonLocked();
    }

    public function setUsername($username)
    {
        $this->username = $username;
        $this->usernameCanonical = Canonicalizer::canonicalizeString($username);

        return $this;
    }

    /**
     * @param \DateTimeImmutable $date
     *
     * @return User
     */
    public function setCredentialsExpireAt(\DateTimeImmutable $date = null)
    {
        $this->credentialsExpireAt = $date;

        return $this;
    }

    /**
     * @param bool $boolean
     *
     * @return User
     */
    public function setCredentialsExpired(bool $boolean)
    {
        $this->credentialsExpired = $boolean;

        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        $this->emailCanonical = Canonicalizer::canonicalizeString($email);

        return $this;
    }

    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function enable()
    {
        $this->enabled = true;

        return $this;
    }

    public function disable()
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * Sets this user to expired.
     *
     * @param Boolean $boolean
     *
     * @return User
     */
    public function setExpired(bool $boolean)
    {
        $this->expired = $boolean;

        return $this;
    }

    /**
     * @param \DateTimeImmutable $date
     *
     * @return User
     */
    public function setExpiresAt(\DateTimeImmutable $date = null)
    {
        $this->expiresAt = $date;

        return $this;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    public function updateLastLogin()
    {
        $this->lastLogin = new \DateTimeImmutable();

        return $this;
    }

    public function setLocked(bool $boolean)
    {
        $this->locked = $boolean;

        return $this;
    }

    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function setPasswordRequestedAt(\DateTimeImmutable $date = null)
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\DateTimeImmutable
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \DateTimeImmutable &&
               $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }

    /**
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function changePassword(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->password = $passwordEncoder->encodePassword($this, $this->plainPassword);
        $this->eraseCredentials();
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getNameType()
    {
        return $this->nameType;
    }

    /**
     * @param AbstractUsernameInterface|null $nameType
     */
    public function setNameType(AbstractUsernameInterface $nameType)
    {
        $this->nameType = $nameType;
    }

    /**
     * @return string
     */
    public function getNameTypeName()
    {
        return $this->nameTypeName;
    }

    /**
     * @param string $nameTypeName
     */
    public function setNameTypeName(string $nameTypeName)
    {
        $this->nameTypeName = $nameTypeName;
    }
}
