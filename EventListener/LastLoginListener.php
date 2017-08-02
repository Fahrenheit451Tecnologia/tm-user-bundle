<?php

namespace TM\UserBundle\EventListener;

use JMS\DiExtraBundle\Annotation as DI;
use TM\UserBundle\Repository\RepositoryRegistryInterface;
use TM\UserBundle\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @DI\Service("tm.listener.last_login")
 */
class LastLoginListener implements EventSubscriberInterface
{
    /**
     * @var RepositoryRegistryInterface
     */
    protected $repositoryRegistry;

    /**
     * @DI\InjectParams({
     *     "repositoryRegistry" = @DI\Inject("tm.registry.repository")
     * })
     *
     * @param RepositoryRegistryInterface $repositoryRegistry
     */
    public function __construct(RepositoryRegistryInterface $repositoryRegistry)
    {
        $this->repositoryRegistry = $repositoryRegistry;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        );
    }

    /**
     * @DI\Observe(SecurityEvents::INTERACTIVE_LOGIN)
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof UserInterface) {
            $user->updateLastLogin();
            $this->repositoryRegistry->getUserRepository()->save($user);
        }
    }
}
