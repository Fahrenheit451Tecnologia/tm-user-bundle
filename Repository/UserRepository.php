<?php

namespace TM\UserBundle\Repository;

use Doctrine\ORM\UnitOfWork;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TM\ResourceBundle\Doctrine\ORM\EntityRepository;
use TM\ResourceBundle\Model\UserInterface as TMResourceUserInterface;
use TM\ResourceBundle\Util\Canonicalizer;
use TM\ResourceBundle\Uuid\UuidInterface;
use TM\UserBundle\Model\UserInterface;

abstract class UserRepository extends EntityRepository
{
    /**
     * Create user
     *
     * @param UuidInterface $id
     * @param string $username
     * @param string $password
     * @param string $email
     * @param bool $active
     * @return UserInterface
     */
    public function createUser(
        UuidInterface $id,
        string $username,
        string $password,
        string $email,
        bool $active = false
    ) {
        $class = $this->getClassName();

        /** @var UserInterface $user */
        $user = new $class();
        $user->setId($id);
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);

        if ($active) {
            $user->enable();
        }

        return $user;
    }

    /**
     * Find user by id
     *
     * @param UuidInterface $id
     * @return UserInterface|null
     */
    public function findOneById(UuidInterface $id) /* : ?UserInterface */
    {
        $queryBuilder = $this->createQueryBuilder('u');

        $queryBuilder
            ->where($queryBuilder->expr()->eq('u.id', ':id'))
            ->setParameter('id', $id)
        ;

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Create empty user with id
     *
     * @param UuidInterface $id
     * @return UserInterface
     */
    public function create(UuidInterface $id) : UserInterface
    {
        $class = $this->getClassName();

        return new $class($id);
    }

    /**
     * Find user by username
     *
     * @param string $username
     * @return null|UserInterface
     */
    public function findByUsername(string $username) /* : ?UserInterface */
    {
        $queryBuilder = $this->createQueryBuilder('u');

        $user = $queryBuilder
            ->where($queryBuilder->expr()->eq('u.usernameCanonical', ':username'))
            ->setParameter('username', Canonicalizer::canonicalizeString($username))
            ->getQuery()
            ->getOneOrNullResult();

        return $user;
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return null|UserInterface
     */
    public function findByEmail(string $email)
    {
        $queryBuilder = $this->createQueryBuilder('u');

        $user = $queryBuilder
            ->where($queryBuilder->expr()->eq('u.emailCanonical', ':email'))
            ->setParameter('email', Canonicalizer::canonicalizeString($email))
            ->getQuery()
            ->getOneOrNullResult();

        return $user;
    }

    /**
     * Get user by username or throw exception
     *
     * @param string $username
     * @return UserInterface
     */
    public function getByUsername(string $username) : UserInterface
    {
        if (null === $user = $this->findByUsername($username)) {
            throw new NotFoundHttpException(sprintf(
                'User "%s" can not be found',
                $username
            ));
        }

        return $user;
    }

    /**
     * @param UserInterface|TMResourceUserInterface $user
     * @return void
     */
    public function save(TMResourceUserInterface $user) /* : void */
    {
        if (!$this->_em->contains($user)) {
            $this->_em->persist($user);
        }

        $this->_em->flush();
    }

    /**
     * @param UserInterface $user
     * @return void
     */
    public function delete(UserInterface $user) /* : void */
    {
        $this->_em->remove($user);
        $this->_em->flush();
    }
}