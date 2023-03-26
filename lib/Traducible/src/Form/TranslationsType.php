<?php

declare(strict_types=1);

namespace Stulipan\Traducible\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        $builder->addEventSubscriber($this->translationsListener);

        $currentLocale = $options['__currentLocale'];
        $showAllLocales = null != $currentLocale ? false : true;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options, $showAllLocales, $currentLocale) {
            $entity = $event->getData();
            $form = $event->getForm();

            $entity = $entity->getOwner();
            $translations = $entity->getTranslations();

            if ($showAllLocales) {
                if (count($translations) > 0) {
                    foreach ($translations as $translation) {

                        $form->add($translation->getLocale(), TranslationItemType::class, [
                            'data_class' => $options['__translationClass'],
                            '__locale' => $translation->getLocale(),
                        ]);
                    }
                }
            } else {
                /** @ var ProductBadgeTranslation $translation */
                foreach ($translations as $translation) {
                    if ($translation->getLocale() == $currentLocale) {

                        $form->add($translation->getLocale(), TranslationItemType::class, [
                            'data_class' => $options['__translationClass'],
                            '__locale' => $translation->getLocale(),
                        ]);
                    }
                }
            }

        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            '__translationClass' => '',
            '__currentLocale' => '',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'traducible_translations';
    }
}