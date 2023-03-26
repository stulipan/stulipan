<?php

declare(strict_types=1);

namespace Stulipan\Traducible\Form;

use ReflectionClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $properties = $this->fetchTranslatedProperties($options['data_class']);

        foreach ($properties as $property) {
            $builder->add($property, TextType::class, [
                'label' => $property,
                'attr' => ['autocomplete' => 'off'],
            ]);
        }

        $builder->getForm();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            '__locale' => ''
        ]);
    }

    private function fetchTranslatedProperties(string $entityClass)
    {
        $restrictedProperties = ['id', 'locale', 'translatable'];
        $entityProperties = (new ReflectionClass($entityClass))->getProperties();

        $properties = [];
        foreach ($entityProperties as $property) {
            if (!in_array($property->name, $restrictedProperties, true)) {
                array_push($properties, $property->name);
            }
        }

        return $properties;
    }
}
