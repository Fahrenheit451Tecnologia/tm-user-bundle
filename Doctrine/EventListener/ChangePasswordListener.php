<?php

namespace TM\UserBundle\Doctrine\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\UnitOfWork;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use TM\UserBundle\Model\UserInterface;

/**
 * @DI\Service("tm.doctrine_listener.change_password")
 * @DI\DoctrineListener(
 *     events = {"prePersist","preUpdate"},
 *     connection = "default",
 *     lazy = true,
 *     priority = 0
 * )
 */
class ChangePasswordListener
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @DI\InjectParams({
     *     "passwordEncoder" = @DI\Inject("security.password_encoder")
     * })
     *
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->changePassword($args);
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->changePassword($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    private function changePassword(LifecycleEventArgs $args)
    {
        $user = $args->getEntity();

        if (!$user instanceof UserInterface) {
            return;
        }

        if (0 === strlen($password = $user->getPlainPassword())) {
            return;
        }

        $user->changePassword($this->passwordEncoder);

        /** @var EntityManager $objectManager */
        $objectManager = $args->getObjectManager();
        $unitOfWork = $objectManager->getUnitOfWork();
        $meta = $objectManager->getClassMetadata(get_class($user));

        if (UnitOfWork::STATE_NEW !== $unitOfWork->getEntityState($user)) {
            $unitOfWork->recomputeSingleEntityChangeSet($meta, $user);
        }
    }
}