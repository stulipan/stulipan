<?php

namespace App\Controller\Utils;

use App\Entity\Product\ProductCategory;
use App\Entity\VatRate;
use App\Entity\VatValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
class SettingsController extends AbstractController
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
     * @Route("/settings/", name="settings-edit")
     */
    public function editSettings(Request $request)
    {
        $configDirectories = [__DIR__.'/../../Config'];
    
        $fileLocator = new FileLocator($configDirectories);
        $locatedFile = $fileLocator->locate('settings.yaml', null, false);
        
        if (count($locatedFile) === 1) {
            $values = Yaml::parseFile($locatedFile[0]);
        } else {
            throw new \Error( sprintf('HIBA: Multiple %s files were found. Make sure you have only one %s file in your /src/Config folder!', 'settings.yaml'));
        }
        
        $formBuilder = $this->createFormBuilder();
        $parameters = $values['parameters'];
        
        $notNullConstraint = [ new NotNull([
            'message' => 'Üres mező...'
        ])];
    
        foreach ($parameters as $key => $value) {
            if (is_string($value) || $value === null) {
                $formBuilder->add($key, TextType::class, [
                    'mapped' => false,
                    'label' => ucfirst(str_replace("_"," ",$key)),
                    'data' => $value,
                    'constraints' => $notNullConstraint,
                    'invalid_message' => 'Hibás érték: Szöveget kell megadnod. Ha nem akarsz semmit, akkor nyomj egy space-t.',
                ]);
            }
            if (is_int($value)) {
                $formBuilder->add($key, NumberType::class, [
                    'mapped' => false,
                    'input' => 'number',
                    'scale' => 0,
                    'label' => ucfirst(str_replace("_"," ",$key)),
                    'data' => $value,
                    'constraints' => $notNullConstraint,
                    'invalid_message' => 'Hibás érték: Számot kell megadnod.',
                ]);
            }
            if (is_array($value)) {
                if ($value['type'] === 'VatRate') {
                    $vatRate = $this->getDoctrine()->getRepository(VatRate::class)->find($value['value']);
                    $formBuilder->add($key, EntityType::class, [
                        'mapped' => false,
                        'class' => VatRate::class,
                        'label' => 'ÁFA',
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
                    $category = $this->getDoctrine()->getRepository(ProductCategory::class)->find($value['value']);
                    $formBuilder->add($key, EntityType::class, [
                        'mapped' => false,
                        'class' => ProductCategory::class,
                        'label' => 'Honnan vegye az ajándékokat (melyik kategóriából)',
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
            }
            
        }
        $form = $formBuilder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($parameters as $key => $value) {
                $data[$key] = $form->get($key)->getData();
            }
            foreach ($data as $key => $value) {
                if (is_string($value)) {
                    $parameters[$key] = $value;
                }
                if (is_numeric($value)) {
                    $parameters[$key] = (int) $value;
                }
                if ($value instanceof VatRate) {
                    $parameters[$key]['value'] = $value->getId();
                }
                if ($value instanceof ProductCategory) {
                    $parameters[$key]['value'] = $value->getId();
                }
                if ($value == '') {
                    $parameters[$key] = $value;
                }
                
            }
            
            $values['parameters'] = $parameters;
            $newSettings = Yaml::dump($values,3);
            file_put_contents($locatedFile[0], $newSettings);
        
            $this->addFlash('success', 'Beállítások sikeresen elmentve!');
            return $this->redirectToRoute('settings-edit');
        }
        
        $title = 'Beállítások';
        return $this->render('admin/settings/settings.html.twig', [
            'form' => $form->createView(),
            'parameters' => $parameters,
            'title' => $title,
        ]);
    }
}