<?php
/**
 *
 */

namespace TM\UserBundle\Repository;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use JMS\DiExtraBundle\Annotation as DI;
use TM\UserBundle\Repository\UserRepository;

/**
 * Class RepositoryProvider
 * @package TM\UserBundle\Repository
 *
 * @DI\Service("tm.provider.user_repository")
 */
class RepositoryProvider
{

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var string
     */
    private $className;

    /**
     * @DI\InjectParams({
     *     "doctrine" = @DI\Inject("doctrine")
     *     "className" = @DI\Inject("%tm.model.user.class")
     * })
     *
     * RepositoryProvider constructor.
     * @param ManagerRegistry $doctrine
     * @param string $className
     */
    public function __construct(ManagerRegistry $doctrine, string $className)
    {
        $this->doctrine = $doctrine;
        $this->className = $className;
    }

    /**
     * @return ObjectRepository|UserRepository
     */
    public function getUserRepository()
    {
        return $this
            ->doctrine
            ->getManagerForClass($this->className)
            ->getRepository($this->className);
    }

}