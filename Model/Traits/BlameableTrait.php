<?php

namespace TM\UserBundle\Model\Traits;


use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use TM\AppBundle\Model\UserInterface;

trait BlameableTrait
{

    /**
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="TM\AppBundle\Model\UserInterface")
     * @ORM\JoinColumn(name="own_by", referencedColumnName="id")
     */
    private $ownBy;

    /**
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="TM\AppBundle\Model\UserInterface")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;

    /**
     * @return UserInterface
     */
    public function getOwnBy()
    {
        return $this->ownBy;
    }

    /**
     * @return UserInterface
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * @param $updatedBy
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;
    }

}