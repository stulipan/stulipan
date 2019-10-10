<?php

declare(strict_types=1);

namespace App\Event;

//use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * This has to be configured in services.yaml
 */
class TimestampableModel
{
    /**
     * Add createdAt timestamp
     *
     * @param LifeCycleEventArgs $args
     */
    public function prePersist(LifeCycleEventArgs $args)
    {
        $entity = $args->getEntity();
//        $entity = $args->getObject();  // a masik libraryvel lasd fent use kikommentelve
        if ($this->hasTimestampableModelCreateTrait($entity) === true) {
            $entity->setCreatedAt(new \DateTime());
            $entity->setUpdatedAt(new \DateTime());
        }
    }
    private function hasTimestampableModelCreateTrait($entity)
    {
        if (array_key_exists('App\Entity\TimestampableTrait', $this->class_uses_deep($entity))) {
            return true;
        }
        return false;
    }

    /**
     * Add updatedAt timestamp
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifeCycleEventArgs $args)
    {
        $entity = $args->getEntity();
//        $entity = $args->getObject();  // a masik libraryvel lasd fent use kikommentelve
        if ($this->hasTimestampableModelUpdateTrait($entity) === true) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }
    private function hasTimestampableModelUpdateTrait($entity)
    {
        if (array_key_exists('App\Entity\TimestampableTrait', $this->class_uses_deep($entity))) {
            return true;
        }
        return false;
    }
    
    /**
     * To get ALL traits including those used by parent classes and other traits, use this function:
     */
    private function class_uses_deep($class, $autoload = true) {
        $traits = [];
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while($class = get_parent_class($class));
        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }
        return array_unique($traits);
    }
}