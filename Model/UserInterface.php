<?php

namespace TM\UserBundle\Model;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use TM\ResourceBundle\Loggable\LoggableInterface;
use TM\ResourceBundle\Model\Traits\HasClientGeneratedIdInterface;
use TM\ResourceBundle\Model\UserInterface as TMResourceUserInterface;
use TM\ResourceBundle\Model\ValueObject\Locale;
use TM\UserBundle\Model\ValueObject\AbstractUsernameInterface;

interface UserInterface extends
    HasClientGeneratedIdInterface,
    TMResourceUserInterface,
    LoggableInterface,
    AdvancedUserInterface,
    \Serializable
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * Sets the username.
     *
     * @param string $username
     *
     * @return self
     */
    public function setUsername($username);

    /**
     * Gets the canonical username in search and sort queries.
     *
     * @return string
     */
    public function getUsernameCanonical();

    /**
     * Gets email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail($email);

    /**
     * Gets the canonical email in search and sort queries.
     *
     * @return string
     */
    public function getEmailCanonical();

    /**
     * Gets the plain password.
     *
     * @return string
     */
    public function getPlainPassword();

    /**
     * Sets the plain password.
     *
     * @param string $password
     *
     * @return self
     */
    public function setPlainPassword($password);

    /**
     * Set whether user is enabled or not
     *
     * @param bool $enabled
     * @return self
     */
    public function setEnabled(bool $enabled);

    /**
     * Enable user
     *
     * @return self
     */
    public function enable();

    /**
     * Disable user
     *
     * @return self
     */
    public function disable();

    /**
     * Sets the locking status of the user.
     *
     * @param boolean $boolean
     *
     * @return self
     */
    public function setLocked(bool $boolean);

    /**
     * Gets the confirmation token.
     *
     * @return string
     */
    public function getConfirmationToken();

    /**
     * Sets the confirmation token
     *
     * @param string $confirmationToken
     *
     * @return self
     */
    public function setConfirmationToken($confirmationToken);

    /**
     * Sets the timestamp that the user requested a password reset.
     *
     * @param null|\DateTimeImmutable $date
     *
     * @return self
     */
    public function setPasswordRequestedAt(\DateTimeImmutable $date = null);

    /**
     * Checks whether the password reset request has expired.
     *
     * @param integer $ttl Requests older than this many seconds will be considered expired
     *
     * @return boolean true if the user's password request is non expired, false otherwise
     */
    public function isPasswordRequestNonExpired($ttl);

    /**
     * Sets the last login time
     *
     * @return self
     */
    public function updateLastLogin();

    /**
     * Change user password and erase credentials
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function changePassword(UserPasswordEncoderInterface $passwordEncoder);

    /**
     * Set user locale
     *
     * @param Locale $locale
     * @return self
     */
    public function setLocale(Locale $locale);

    /**
     * Get user locale
     *
     * @return Locale
     */
    public function getLocale();

    /**
     * Get User's name type
     * @return AbstractUsernameInterface
     */
    public function getNameType();

    /**
     * Set User name type
     * @param AbstractUsernameInterface $nameType
     */
    public function setNameType(AbstractUsernameInterface $nameType);

    /**
     * Get Name type name
     * @return string|null
     */
    public function getNameTypeName();

    /**
     * Set name type name
     * @param string $nameTypeName
     */
    public function setNameTypeName(string $nameTypeName);
}
