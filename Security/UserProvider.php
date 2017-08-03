<?php

namespace TM\UserBundle\Security;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use TM\UserBundle\Model\UserInterface;
use TM\UserBundle\Repository\RepositoryProvider;

/**
 * @DI\Service("tm.security.provider.user")
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var RepositoryProvider
     */
    protected $userRepositoryProvider;

    /**
     * @DI\InjectParams({
     *     "userRepositoryProvider" = @DI\Inject("tm.provider.user_repository")
     * })
     *
     * @param RepositoryProvider $repositoryProvider
     */
    public function __construct(RepositoryProvider $repositoryProvider)
    {
        $this->userRepositoryProvider = $repositoryProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($usernameOrEmail)
    {

        if(preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)){
            if (null === $user = $this->userRepositoryProvider->getUserRepository()->findByEmail($usernameOrEmail)) {
                throw new UsernameNotFoundException(sprintf(
                    'Email "%s" does not exist.',
                    $usernameOrEmail
                ));
            }

            return $user;
        }

        if (null === $user = $this->userRepositoryProvider->getUserRepository()->findByUsername($usernameOrEmail)) {
            throw new UsernameNotFoundException(sprintf(
                'Username "%s" does not exist.',
                $usernameOrEmail
            ));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(SecurityUserInterface $user)
    {
        if (!$user instanceof UserInterface || !$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf(
                'Expected an instance of %s, but got "%s".',
                UserInterface::class,
                get_class($user)
            ));
        }

        if (null === $reloadedUser = $this->userRepositoryProvider->getUserRepository()->findOneById($user->getId())) {
            throw new UsernameNotFoundException(sprintf(
                'User with ID "%s" could not be reloaded.',
                $user->getId()->toString()
            ));
        }

        return $reloadedUser;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        return UserInterface::class === $class || is_subclass_of($class, UserInterface::class);
    }
}