<?php

namespace App\Repository;

use App\Entity\ImageEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class ImageEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImageEntity::class);
    }

    /**
     * @return\Doctrine\ORM\Query
     */
    public function findAllStoreImagesQB()
    {
         return $this->createQueryBuilder('o')
             ->andWhere('o.type = :type')
             ->setParameter('type', ImageEntity::STORE_IMAGE)
             ->orderBy('o.id', 'DESC')
             ;
    }

}