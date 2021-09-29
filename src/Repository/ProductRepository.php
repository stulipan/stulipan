<?php

namespace App\Repository;

use App\Entity\DateRange;
use App\Entity\Product\Product;
use App\Entity\Product\ProductCategory;
use App\Entity\Product\ProductStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Exception;


class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Used with PagerFanta in ProductController.
     *
     * Fetches products
     *  - using some filtering criteria
     * @param array $filters             It is used to filter entries.
     *                                  If no filter is set, it will count all entries.
     *             [
     *                  'searchTerm => 'valami'
     *                  'status' => 1  // 1 = enabled, see db.
     *             ]
     * @return Query
     * @throws Exception
     */
    public function findAllQuery($filters = [])
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.status IS NOT NULL')
            ->orderBy('p.createdAt', 'DESC')
        ;

        if (is_array($filters)) {
            if (array_key_exists('searchTerm', $filters) && $filters['searchTerm']) {
                $searchTerm = strtolower($filters['searchTerm']);
                $qb->andWhere('p.id LIKE :id OR 
                                LOWER(p.name) LIKE :name OR 
                                LOWER(p.sku) LIKE :sku
                                ')
                    ->setParameter('id', '%'.$searchTerm.'%')
                    ->setParameter('name', '%'.$searchTerm.'%')
                    ->setParameter('sku', '%'.$searchTerm.'%')
                ;
            }
            if (array_key_exists('status', $filters) && $filters['status']) {
                $status = $filters['status'];
                $status = $this->getEntityManager()->getRepository(ProductStatus::class)->findOneBy(['shortcode' => $status]);

                $qb->andWhere('p.status = :status')
                    ->setParameter('status', $status)
                ;
            }
        }
        $query = $qb->getQuery();
        return $query;
    }

    /**
     * Counts all Products.
     * @param array $filters            It is used to filter products.
     *                                  If no filter is set, it will count all orders.
     *             [
     *                'status' => 1  // 1 = enabled, see db.
     *             ]
     * @return array|null
     * @throws Exception
     */
    public function countAll($filters = [])
    {
        $qb = $this
            ->createQueryBuilder('p')
            ->select('COUNT(p.id) as count')   // COUNT
            ->andWhere('p.status IS NOT NULL')
            ->orderBy('p.createdAt', 'DESC')
        ;

        if (is_array($filters)) {
            if (array_key_exists('searchTerm', $filters) && $filters['searchTerm']) {
                $searchTerm = strtolower($filters['searchTerm']);
                $qb->andWhere('p.id LIKE :id OR 
                                LOWER(p.name) LIKE :name OR 
                                LOWER(p.sku) LIKE :sku
                                ')
                    ->setParameter('id', '%'.$searchTerm.'%')
                    ->setParameter('name', '%'.$searchTerm.'%')
                    ->setParameter('sku', '%'.$searchTerm.'%')
                ;
            }
            if (array_key_exists('status', $filters) && $filters['status']) {
                $status = $filters['status'];
                $status = $this->getEntityManager()->getRepository(ProductStatus::class)->findOneBy(['shortcode' => $status]);

                $qb->andWhere('p.status = :status')
                    ->setParameter('status', $status)
                ;
            }
        }
        $query = $qb->getQuery()->getSingleResult();
        return $query['count'];
    }

    /**
     * @param ProductCategory $category
     * @return mixed
     */
    public function retrieveByCategory(ProductCategory $category): array
    {
        $rep = $this->getEntityManager()->getRepository(ProductStatus::class);
        $enabled = $rep->findOneBy(['shortcode' => ProductStatus::STATUS_ENABLED]);
        $unavailable = $rep->findOneBy(['shortcode' => ProductStatus::STATUS_UNAVAILABLE]);

        $qb = $this->createQueryBuilder('p')
            ->innerJoin('p.categories','c')
            ->where('p.status = :status1')
            ->orWhere('p.status = :status2')
            ->andWhere('c.id = :categoryId')

            ->setParameter('status1', $enabled)
            ->setParameter('status2', $unavailable)
            ->setParameter('categoryId',$category->getId())
            ->orderBy('p.rank', 'ASC')
            ->getQuery();

        return $qb->execute();
	}

    /**
     * @return\Doctrine\ORM\Query
     */
    public function findAllOrdered(?int $limit)
    {
        $limit+=1;
        $qb = $this->createQueryBuilder('p')
//            ->andWhere('p.enabled = :enabled')
//            ->setParameter('enabled', 1)
             ->orderBy('p.rank', 'ASC')
            ->setMaxResults($limit)
        ;
        return $qb->getQuery()->execute();
    }

}