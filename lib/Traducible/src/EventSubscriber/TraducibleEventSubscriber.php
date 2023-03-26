<?php

namespace Stulipan\Traducible\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ObjectManager;
use ReflectionClass;
use Stulipan\Traducible\Builder\LocaleBuilder;
use Stulipan\Traducible\Entity\TraducibleInterface;
use Stulipan\Traducible\Entity\TranslationInterface;

final class TraducibleEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    public const LOCALE = 'locale';

    private $translatableFetchMode;

    private $translationFetchMode;

    private $localeBuilder;

    public function __construct(LocaleBuilder $localeProvider, string $translatableFetchMode, string $translationFetchMode)
    {
        $this->localeBuilder = $localeProvider;
        $this->translatableFetchMode = $this->convertFetchString($translatableFetchMode);
        $this->translationFetchMode = $this->convertFetchString($translationFetchMode);
    }

    /**
     * Adds mapping to the translatable and translations.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if (! $classMetadata->reflClass instanceof ReflectionClass) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if ($classMetadata->isMappedSuperclass) {
            return;
        }

        if (is_a($classMetadata->reflClass->getName(), TraducibleInterface::class, true)) {
            $this->mapTranslatable($classMetadata);
        }

        if (is_a($classMetadata->reflClass->getName(), TranslationInterface::class, true)) {
            $this->mapTranslation($classMetadata, $loadClassMetadataEventArgs->getObjectManager());
        }
    }

    public function postLoad(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->setLocales($lifecycleEventArgs);
    }

    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->setLocales($lifecycleEventArgs);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata, Events::postLoad, Events::prePersist];
    }

    /**
     * Convert string FETCH mode to required string
     * @param string|int
     */
    private function convertFetchString($fetchMode): int
    {
        if (is_int($fetchMode)) {
            return $fetchMode;
        }

        if ($fetchMode === 'EAGER') {
            return ClassMetadataInfo::FETCH_EAGER;
        }

        if ($fetchMode === 'EXTRA_LAZY') {
            return ClassMetadataInfo::FETCH_EXTRA_LAZY;
        }

        return ClassMetadataInfo::FETCH_LAZY;
    }

    private function mapTranslatable(ClassMetadataInfo $classMetadataInfo): void
    {
        if ($classMetadataInfo->hasAssociation('translations')) {
            return;
        }

        $classMetadataInfo->mapOneToMany([
            'fieldName' => 'translations',
            'mappedBy' => 'translatable',
            'indexBy' => self::LOCALE,
            'cascade' => ['persist', 'merge', 'remove'],
            'fetch' => $this->translatableFetchMode,
            'targetEntity' => $classMetadataInfo->getReflectionClass()
                ->getMethod('getTranslationEntityClass')
                ->invoke(null),
            'orphanRemoval' => true,
        ]);
    }

    private function mapTranslation(ClassMetadataInfo $classMetadataInfo, ObjectManager $objectManager): void
    {
        if (! $classMetadataInfo->hasAssociation('translatable')) {
            $targetEntity = $classMetadataInfo->getReflectionClass()
                ->getMethod('getTranslatableEntityClass')
                ->invoke(null);

            /** @var ClassMetadataInfo $classMetadata */
            $classMetadata = $objectManager->getClassMetadata($targetEntity);

            $singleIdentifierFieldName = $classMetadata->getSingleIdentifierFieldName();

            $classMetadataInfo->mapManyToOne([
                'fieldName' => 'translatable',
                'inversedBy' => 'translations',
                'cascade' => ['persist', 'merge'],
                'fetch' => $this->translationFetchMode,
                'joinColumns' => [[
                    'name' => 'translatable_id',
                    'referencedColumnName' => $singleIdentifierFieldName,
                    'onDelete' => 'CASCADE',
                ]],
                'targetEntity' => $targetEntity,
            ]);
        }

        $name = $classMetadataInfo->getTableName() . '_unique_translation';
        if (! $this->hasUniqueTranslationConstraint($classMetadataInfo, $name) &&
            $classMetadataInfo->getName() === $classMetadataInfo->rootEntityName) {
            $classMetadataInfo->table['uniqueConstraints'][$name] = [
                'columns' => ['translatable_id', self::LOCALE],
            ];
        }

        if (! $classMetadataInfo->hasField(self::LOCALE) && ! $classMetadataInfo->hasAssociation(self::LOCALE)) {
            $classMetadataInfo->mapField([
                'fieldName' => self::LOCALE,
                'type' => 'string',
                'length' => 5,
            ]);
        }
    }

    private function setLocales(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getEntity();
        if (! $entity instanceof TraducibleInterface) {
            return;
        }

        $currentLocale = $this->localeBuilder->provideCurrentLocale();
        if ($currentLocale) {
            $entity->setCurrentLocale($currentLocale);
        }

        $fallbackLocale = $this->localeBuilder->provideFallbackLocale();
        if ($fallbackLocale) {
            $entity->setDefaultLocale($fallbackLocale);
        }
    }

    private function hasUniqueTranslationConstraint(ClassMetadataInfo $classMetadataInfo, string $name): bool
    {
        return isset($classMetadataInfo->table['uniqueConstraints'][$name]);
    }
}