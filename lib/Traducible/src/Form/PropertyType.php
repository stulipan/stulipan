<?php

declare(strict_types=1);

namespace Stulipan\Traducible\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * !!! NINCS HASZNALVA
 *  Ha meg tudnam oldani, hogy egy nem mapped mezohoz bekossek egy entity-t akkor jarhato lehetne ez az ut
 */
class PropertyType extends AbstractType
{
    private $em;
    public function __constructor(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $translationClass = $options['__translationClass'];
//        $translatableEntity = $options['__translatableEntity']; // badge
//        $currentLocale = $options['__currentLocale'];
//        $showAllLocales = null != $currentLocale ? false : true;

        $propertyName = $builder->getName();

        $translation = $this->fetchTranslatedProperties($options['__currentLocale'], $options['__translatableEntity']);

//        $builder->add('name', TextType::class, [
//            'label' => 'name',
//            'attr' => ['autocomplete' => 'off'],
//            'data' => $translation,
//            'data_class' => $translationClass,
//        'mapped' => true,
//        ]);
        $builder->create($translation->getLocale(), TranslationItemType::class, [
            'data' => $translation,
            'data_class' => $translationClass,
            'mapped' => true,
        ]);
        $builder->getForm();

//        dd($builder);
//        $builder->setData($translation);
//        dd($builder->getData());


//        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
//            $entity = $event->getData();
//            $form = $event->getForm();
//
//            dd($entity);
////            $entity = $entity->getOwner();
//            $translations = $entity->getTranslations();
//
//            if ($showAllLocales) {
//                if (count($translations) > 0) {
//                    foreach ($translations as $translation) {
//
//                        $form->add($translation->getLocale(), TranslationItemType::class, [
//                            'data_class' => $options['__translationClass'],
//                            '__locale' => $translation->getLocale(),
//                        ]);
//                    }
//                }
//            } else {
//                /** @ var ProductBadgeTranslation $translation */
//                foreach ($translations as $translation) {
//                    if ($translation->getLocale() == $currentLocale) {
//
//                        $form->add($translation->getLocale(), TranslationItemType::class, [
//                            'data_class' => $options['__translationClass'],
//                            '__locale' => $translation->getLocale(),
//                        ]);
//                    }
//                }
//            }
//
//        });

//        $builder->setFormFactory(TextType::class, 'name')
//        $builder->setPropertyPath($propertyName);
//        $builder->setData($translation);

//        dd($translation);



//        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            '__translationClass' => '',
            '__property' => '',
            '__translatableEntity' => '',
            '__currentLocale' => '',
        ]);
    }

    private function fetchTranslatedProperties(string $locale, object $translatableEntity)
    {
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->eq('locale', $locale));

        /** @var ArrayCollection $translations */
        $translations = $translatableEntity->getTranslations();

        if ($translations->matching($criteria)->isEmpty()) {
            throw new NotFoundHttpException('HIBA: Nem talalt ilyen forditast!');
        }
        return $translations->matching($criteria)->first();
    }
}