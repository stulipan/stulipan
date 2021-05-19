<?php

namespace App\Repository;

use App\Entity\CmsPage;
use App\Entity\CmsSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class CmsSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CmsSection::class);
    }

    /**
     * @return\Doctrine\ORM\Query
     */
    public function findAllQB()
    {
         return $this->createQueryBuilder('o')
             ->orderBy('o.name', 'ASC')
             ;
    }

}