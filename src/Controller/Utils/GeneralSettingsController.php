<?php

namespace App\Controller\Utils;

use App\Entity\Geo\GeoCountry;
use App\Entity\Product\ProductCategory;
use App\Entity\VatRate;
use App\Entity\VatValue;
use App\Services\StoreSettings;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Error;
use Liip\ImagineBundle\Config\FilterInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Psr\Container\ContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Yaml\Yaml;

/**
 * @Route("/admin")
 * @IsGranted("ROLE_MANAGE_SETTINGS")
 */
class GeneralSettingsController extends AbstractController
{
    /**
     * The value means hours. A customer can pick delivery date only after:
     * \DateTime('now')+DELIVERY_DATE_HOUR_OFFSET
     */
    public const DELIVERY_DATE_HOUR_OFFSET = 4; // ha 1, akkor ez a default dátum típus;

    public const ORDER_NUMBER_FIRST_DIGIT = 2; // minden rendeles ilyen lesz: 5xxx-xxxxx
    public const ORDER_NUMBER_RANGE = 10000;
    
    private $defaultVatRate;
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
//        $this->defaultVatRate = $defaultVatRate;
        $this->parameterBag = $parameterBag;
    }
    
    /**
     * @Route("/settings/configuration", name="settings-configuration")
     *
     * Param $storeSettingsDirectory comes from services.yaml
     * Param $generalSettingsFile comes from services.yaml
     */
    public function editSettings(Request $request, StoreSettings $settings, string $storeSettingsDirectory, string $generalSettingsFile)
    {
        $configDirectories = [$storeSettingsDirectory];

        $fileLocator = new FileLocator($configDirectories);
        $locatedFile = $fileLocator->locate($generalSettingsFile, null, false);

        if (count($locatedFile) === 1) {
            $values = Yaml::parseFile($locatedFile[0]);
        } else {
            throw new Error( sprintf('HIBA: Multiple %s files were found. Make sure you have only one %s file in your %s folder!', $generalSettingsFile, $storeSettingsDirectory));
        }
        
        $formBuilder = $this->createFormBuilder();
        $parameters = $values['parameters'];
        
        $notNullConstraint = [ new NotNull([
            'message' => 'Üres mező...'
        ])];
    
        foreach ($parameters as $group => $params) {
            foreach ($params as $key => $value) {
//                dd($key);
                if (is_array($value)) {
                    if ($key === 'label' || $value['type'] === 'Hidden') {
                        continue;
                    }
                }
                if (is_array($value)) {
                    if ($value['type'] === 'Text') {
                        if (isset($value['label']) && $settings->get($value['label'])) {
                            $label = $settings->get($value['label']);
                        } else {
                            $label = isset($value['label']) ? $value['label'] : ucfirst(str_replace("-", " ", $key));
                        }
                        if (isset($value['choices'])) {
                            /**
                             * If 'choices' is defined in the settings.yaml file, then this input is a dropdown.
                             */
                            $formBuilder->add($group.'_'.$key, ChoiceType::class, [
                                'mapped' => false,
                                'required' => false,
                                'label' => $label,
                                'data' => $value['content'],
                                'choices' => $value['choices'],
                                'placeholder' => 'Válassz...',
                                'multiple' => false,
                                'empty_data' => ' ',
                                //                    'constraints' => $notNullConstraint,
                                'invalid_message' => 'Választanod kell egy opciót.',
                            ]);
                        } else {
                            /**
                             * Else the input is just a regular text input field.
                             */
                            $formBuilder->add($group.'_'.$key, TextType::class, [
                                'mapped' => false,
                                'required' => false,
//                            'label' => ucfirst(str_replace("-", " ", $key)),
                                'label' => $label,
                                'data' => $value['content'],
                                'empty_data' => ' ',
                                //                    'constraints' => $notNullConstraint,
                                'invalid_message' => 'Hibás érték: Szöveget kell megadnod. Ha nem akarsz semmit, akkor nyomj egy space-t.',
                            ]);
                        }
                    }
                }
                if (is_array($value)) {
                    if ($value['type'] === 'Boolean') {
                        $label = isset($value['label']) ? $value['label'] : ucfirst(str_replace("-", " ", $key));
                        $formBuilder->add($group.'_'.$key, CheckboxType::class, [
                            'mapped' => false,
                            'required' => false,
                            'label' => $label,
                            'data' => $value['content']
                        ]);
                    }
                }
                if (is_array($value)) {
                    if ($value['type'] === 'Number') {
                        $label = isset($value['label']) ? $value['label'] : ucfirst(str_replace("-", " ", $key));
                        $formBuilder->add($group.'_'.$key, NumberType::class, [
                            'mapped' => false,
                            'input' => 'number',
                            'scale' => 0,
                            'label' => $label,
                            'data' => $value['content'],
                            'constraints' => $notNullConstraint,
                            'invalid_message' => 'Hibás érték: Számot kell megadnod.',
                        ]);
                    }
                }
                if (is_array($value)) {
                    if ($value['type'] === 'DateTime') {
                        $label = isset($value['label']) ? $value['label'] : ucfirst(str_replace("-", " ", $key));
                        $formBuilder->add($group.'_'.$key, TextType::class, [
                            'mapped' => false,
                            'label' => $label,
                            'data' => $value['content'],
                            'constraints' => $notNullConstraint,
                            'attr' => ['placeholder' => 'ÉÉÉÉ-HH-NN', 'autocomplete' => 'off', 'class' => 'JS--inputDate'],
                            'invalid_message' => 'Hibás érték: Számot kell megadnod.',
                        ]);
                    }
                }
                if (is_array($value)) {
                    if ($value['type'] === 'VatRate') {
                        $vatRate = $this->getDoctrine()->getRepository(VatRate::class)->find($value['content']);
                        $label = isset($value['label']) ? $value['label'] : ucfirst(str_replace("-", " ", $key));
                        $formBuilder->add($group.'_'.$key, EntityType::class, [
                            'mapped' => false,
                            'class' => VatRate::class,
                            'label' => $label,
                            'placeholder' => 'Válassz...',
                            'choice_label' => 'name',
                            'query_builder' => function (EntityRepository $entityRepository) {
                                return $entityRepository->createQueryBuilder('v')
                                    ->orderBy('v.name');
                            },
                            'data' => $vatRate,
                            'constraints' => $notNullConstraint,
                        ]);
                    }
                    if ($value['type'] === 'ProductCategory') {
                        $category = $this->getDoctrine()->getRepository(ProductCategory::class)->find($value['content']);
                        $label = isset($value['label']) ? $value['label'] : ucfirst(str_replace("-", " ", $key));
                        $formBuilder->add($group.'_'.$key, EntityType::class, [
                            'mapped' => false,
                            'class' => ProductCategory::class,
//                            'label' => 'Honnan vegye az ajándékokat (melyik kategóriából)',
                            'label' => $label,
                            'placeholder' => 'Válassz...',
                            'choice_label' => 'name',
                            'query_builder' => function (EntityRepository $entityRepository) {
                                return $entityRepository->createQueryBuilder('v')
                                    ->orderBy('v.name');
                            },
                            'data' => $category,
                            'constraints' => $notNullConstraint,
                        ]);
                    }
                    if ($value['type'] === 'GeoCountry') {
                        $country = $this->getDoctrine()->getRepository(GeoCountry::class)->find($value['content']);
                        $preferredCountries = $this->getDoctrine()->getRepository(GeoCountry::class)->findBy(['alpha2' => 'hu']);
                        $label = isset($value['label']) ? $value['label'] : ucfirst(str_replace("-", " ", $key));
                        $formBuilder->add($group.'_'.$key, EntityType::class, [
                            'mapped' => false,
                            'class' => GeoCountry::class,
                            'label' => $label,
                            'placeholder' => 'Válassz...',
                            'choice_label' => 'name',
                            'query_builder' => function (EntityRepository $entityRepository) {
                                return $entityRepository->createQueryBuilder('c')
                                    ->orderBy('c.name');
                            },
                            'data' => $country,
                            'constraints' => $notNullConstraint,
                            'preferred_choices' => $preferredCountries,
                        ]);
                    }
                }
            }
            
        }
        $form = $formBuilder->getForm();
        $form->handleRequest($request);
//        dd($form);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($parameters as $group => $params) {
                foreach ($params as $key => $value) {
                    // This will skip $key == 'label'
                    if (array_key_exists($group.'_'.$key, $form->all())) {
                        $data[$group][$key] = $form->get($group.'_'.$key)->getData();
                    }
                }
            }
            foreach ($data as $group => $params) {
                foreach ($params as $key => $value) {
                    if (is_string($value)) {
                        $parameters[$group][$key]['content'] = $value;
                    }
                    if (is_numeric($value)) {
                        $parameters[$group][$key]['content'] = (int)$value;
                    }
                    if (is_bool($value)) {
                        $parameters[$group][$key]['content'] = $value;
                    }
                    if (DateTime::createFromFormat('!Y-m-d',$value)) {
                        $parameters[$group][$key]['content'] = (string)$value;
                    }
                    if ($value instanceof VatRate) {
                        $parameters[$group][$key]['content'] = $value->getId();
                    }
                    if ($value instanceof ProductCategory) {
                        $parameters[$group][$key]['content'] = $value->getId();
                    }
                    if ($value === '') {
                        $parameters[$group][$key] = $value;
                    }
                }
            }

            $values['parameters'] = $parameters;
            $newSettings = Yaml::dump($values,4);
            file_put_contents($locatedFile[0], $newSettings);
        
            $this->addFlash('success', 'Beállítások sikeresen elmentve!');
            return $this->redirectToRoute('settings-configuration');
        }
        
        $title = 'Beállítások';
        return $this->render('admin/settings/general.html.twig', [
            'form' => $form->createView(),
            'parameters' => $parameters,
            'title' => $title,
        ]);
    }

    /**
     * @Route("/settings", name="settings-home")
     */
    public function showSettingsDashboard()
    {
        return $this->render('admin/settings/home.html.twig');
    }
}