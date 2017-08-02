<?php

namespace TM\UserBundle\Repository;

interface RepositoryRegistryInterface
{
    /**
     * @return UserRepository
     */
    public function getUserRepository() /* : UserRepository*/;
}