<?php declare(strict_types=1);

namespace TM\RbacBundle\Repository;

use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use TM\UserBundle\Exception\ManagerNotFoundForClassName;

/**
 * Class RepositoryProvider
 * @package TM\RbacBundle\Repository
 *
 * @DI\Service("tm_user.provider.repository")
 */
class RepositoryProvider
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var string
     */
    private $userModelClassName;

    /**
     * @DI\InjectParams({
     *     "registry" = @DI\Inject("doctrine"),
     *     "userModelClassName" = @DI\Inject("%tm.model.user.class")
     *     })
     * @param ManagerRegistry $registry
     * @param string $userModelClassName
     */
    public function __construct(
        ManagerRegistry $registry,
        string $userModelClassName
    ) {
        $this->registry = $registry;
        $this->userModelClassName = $userModelClassName;
    }

    /**
     * @return ObjectRepository|UserRepositoryInterface
     */
    public function getUserRepository() : UserRepositoryInterface
    {
        return $this->getRepository($this->userModelClassName);
    }

    /**
     * @param string $className
     * @return ObjectRepository
     */
    private function getRepository(string $className) : ObjectRepository
    {
        if (null === $manager = $this->registry->getManagerForClass($className)) {
            throw new ManagerNotFoundForClassName($className);
        }

        return $manager->getRepository($className);
    }
}