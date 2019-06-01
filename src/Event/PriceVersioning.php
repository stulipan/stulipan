<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\Product;
use App\Entity\Price;
use App\Entity\nincs;
use App\Entity\VatRate;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * This has to be configured in services.yaml
 */
class PriceVersioning
{
    /**
     * On flush it checks if price has been updated. If so, creates a new Price with the new price
     * and links it to the Product. The old price is updated with an expiredAt date and activated=false
     *
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

//        dd($uow->getScheduledEntityUpdates());
//        dd($uow->getScheduledEntityInsertions());
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Product) {
//                dd($entity);
            }
            if ($entity instanceof Price) {
                $changes = $uow->getEntityChangeSet($entity);
                $vatRate = $em->getRepository(VatRate::class)->find(1);
                $priceBefore = (float)$changes['grossPrice'][0];
                if ((float)$changes['grossPrice'][0] != $changes['grossPrice'][1] && $changes['grossPrice'][0] != null) {
                    $newPrice = new Price();
                    $newPrice->setProduct($entity->getProduct());
                    $newPrice->setGrossPrice($entity->getGrossPrice());
                    $newPrice->setActivated(true);
                    $newPrice->setVatRate($vatRate);
                    $newPrice->setCreatedAt(new \DateTime('now'));
                    $newPrice->setUpdatedAt(new \DateTime('now'));
                    $entity->getProduct()->setPrice($newPrice);

                    $entity->setActivated(false);
                    $entity->setGrossPrice($priceBefore);
                    $entity->setUpdatedAt(new \DateTime('now'));
                    $entity->setExpiredAt(new \DateTime('now'));

                    $em->persist($newPrice);
                    //If you create and persist a new entity you have to execute $unitOfWork->computeChangeSet($classMetadata, $entity)
                    $uow->computeChangeSet($em->getClassMetadata('App:Price'), $newPrice);

                    $em->persist($entity);
                    //if you change primitive fields or associations you have to trigger a re-computation of the changeset of the affected entity.
                    //This can be done by calling $unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $entity)
                    $uow->recomputeSingleEntityChangeSet($em->getClassMetadata('App:Price'), $entity);

                }
//                dd('BBB');
            }
            if ($entity instanceof nincs) {
                $changeSet = $uow->getEntityChangeSet($entity);
//                dd($entity->getProduct());
                dd($entity);
                dd($changeSet['grossPrice']);
                $vatRate = $em->getRepository(VatRate::class)->find(1);
                $priceBefore = (float)$changeSet['grossPrice'][0];

                if (!isset($changeSet['grossPrice'])) {
                    continue;
                }
                $from = $changeSet['grossPrice'][0];
                $to = $changeSet['grossPrice'][1];
                if (isset($changeSet['grossPrice']) && $from != $to) {

//                if ((float)$changes['grossPrice'][0] != $changes['grossPrice'][1]) {
                    $newPrice = new nincs();
                    dd($entity->getSubproduct());
                    $newPrice->setSubproduct($entity->getSubproduct());
                    $newPrice->setGrossPrice($changeSet['grossPrice'][1]);
                    $newPrice->setActivated(true);
                    $newPrice->setVatRate($vatRate);
                    $newPrice->setCreatedAt(new \DateTime('now'));
                    $newPrice->setUpdatedAt(new \DateTime('now'));
                    $entity->getSubproduct()->setPrice($newPrice);

                    dd($entity->getSubproduct());

                    $entity->setActivated(false);
                    $entity->setGrossPrice($priceBefore);
                    $entity->setUpdatedAt(new \DateTime('now'));
                    $entity->setExpiredAt(new \DateTime('now'));

                    $em->persist($newPrice);
                    //If you create and persist a new entity you have to execute $unitOfWork->computeChangeSet($classMetadata, $entity)
                    $uow->computeChangeSet($em->getClassMetadata('App:SubproductPrice'), $newPrice);

//                    $em->persist($entity->getSubproduct());
                    //if you change primitive fields or associations you have to trigger a re-computation of the changeset of the affected entity.
                    //This can be done by calling $unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $entity)
                    $uow->recomputeSingleEntityChangeSet($em->getClassMetadata('App:Subproduct'), $entity->getSubproduct());
//
//                    $em->persist($entity);
                    //if you change primitive fields or associations you have to trigger a re-computation of the changeset of the affected entity.
                    //This can be done by calling $unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $entity)
                    $uow->recomputeSingleEntityChangeSet($em->getClassMetadata('App:SubproductPrice'), $entity);
                }
            }

        }
    }
}