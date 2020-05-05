<?php

namespace App\Repository;

use App\Entity\DateRange;
use App\Entity\Product\Product;
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
     * @param $categoryId
	 * return \Doctrine\ORM\Query
     */
    public function findByCategory($categoryId): array
    {
        // automatically knows to select Products
        // the "p" is an alias you'll use in the rest of the query
        $qb = $this->createQueryBuilder('product')
            ->andWhere('product.categoryId = :cat')
            ->setParameter('cat', $categoryId)
            ->orderBy('product.rank', 'ASC')
            ->getQuery();

        return $qb->execute();
	}

}