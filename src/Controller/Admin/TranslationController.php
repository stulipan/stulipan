<?php
// ebben tároljuk azon funkciókat, amivel kiolvasunk a boltzaras adatbázistáblából

namespace App\Controller\Admin;

use App\Services\TranslationDumper;
use App\Services\TranslationLoader;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_MANAGE_TRANSLATION")
 * @Route("/admin/translation")
 */
class TranslationController extends AbstractController
{
    /**
     * @Route("/{interface?store}/{category}", name="translation-edit-byCategory")
     */
    public function editTranslationByCategory(Request $request, TranslationLoader $loader, TranslationDumper $dumper, $category = 'generic', $interface)
    {
        if ($interface == 'admin') {
            $loader->setDirectory('admin');
        } else {
            $loader->setDirectory('store');
        }

        $resourceFrom = 'messages.en.yaml';
        $resourceTo = 'messages.hu.yaml';

        $catalogueFrom = $loader->load($resourceFrom);
        $catalogueTo = $loader->load($resourceTo);

        // Verify if the $category key exists. If not, throw page not found exception.
        if (array_key_exists($category, $catalogueFrom)) {
            $formBuilder = [$category => $this->createFormBuilder()];
            $level1 = $catalogueFrom[$category];

            // Verify if the value for $category key is an array. If yes, it contains the text resources for this module.
            if (is_array($level1)) {
                foreach ($level1 as $id1 => $level2) {
                    $label = ucfirst(str_replace("-", " ", $id1));
                    
                    if (is_array($level2)) {
                        // This is be displayed as Title for level2 resources
                        // I'm using TextareaType to identify input field that will be used as Title.
                        $formBuilder[$category]->add($category . '_' . $id1, TextType::class, [
                            'mapped' => false,
                            'required' => false,
                            'label' => $id1,  // pass the $id !!
                            'data' => $id1,
                            'empty_data' => null,
                            'block_prefix' => 'this_is_a_title'
                        ]);

                        foreach ($level2 as $id2 => $level3) {
                            $formBuilder[$category]->add($category . '_' . $id1.'_'.$id2, TextareaType::class, [
                                'mapped' => false,
                                'required' => false,
                                'label' => $id1.'_'.$id2,  // pass the $id !!
                                'data' => array_key_exists($id1, $catalogueTo[$category]) && array_key_exists($id2, $catalogueTo[$category][$id1]) ? $catalogueTo[$category][$id1][$id2] : '',
                                'empty_data' => null,
                                'attr' => ['rows' => 1],
                            ]);
                        }
                    } else {
                        // Verify if category exists in $catalogueTo, then extract the values from it
                        // Otherwise, use empty values
                        $current_data = '';
                        if (isset($catalogueTo[$category])) {
                            if (array_key_exists($id1, $catalogueTo[$category])) {
                                $current_data = $catalogueTo[$category][$id1];
                            }
                        }
                        $formBuilder[$category]->add($category . '_' . $id1, TextareaType::class, [
                            'mapped' => false,
                            'required' => false,
                            'label' => $id1,  // pass the $id !!
                            'data' => $current_data,
                            'empty_data' => null,
                            'attr' => ['rows' => 1],
                        ]);
                    }
                }
                $forms[$category] = $formBuilder[$category]->getForm()->createView();
            }
        } else {
            throw $this->createNotFoundException('HIBA! Ez a kategória nem létezik.');
        }

        // Populate $source[] with the original English text resources.
        // $forms[$category][child.vars.label] is associated to $source[$category][$id]
        $level1 = $catalogueFrom[$category];
        if (is_array($level1)) {
            foreach ($level1 as $id1 => $level2) {
                $label = ucfirst(str_replace("-", " ", $id1));

                if (is_array($level2)) {
                    $sourceText[$category][$id1] = $label;
                    foreach ($level2 as $id2 => $level3) {
                        $sourceText[$category][$id1 . '_' . $id2] = $level3;
                        $sourceLabel[$category][$id1 . '_' . $id2] = ucfirst(str_replace("-", " ", $id2));
                    }
                } else {
                    $sourceText[$category][$id1] = $level2;
                    $sourceLabel[$category][$id1] = ucfirst(str_replace("-", " ", $id1));
                }
            }
        }

        // Create navigation tabs
        foreach ($catalogueFrom as $key => $level1) {
            $tabs[$key] = $this->generateUrl('translation-edit-byCategory', ['interface' => $interface, 'category' => $key]);
        }

        $form = $formBuilder[$category]->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $level1 = $catalogueFrom[$category];
            foreach ($level1 as $id1 => $level2) {

                if (is_array($level2)) {
                    foreach ($level2 as $id2 => $level3) {
//                        $data[$category][$id1][$id2] = null || ' ' !== $form->get($category.'_'.$id1.'_'.$id2)->getData() ? $form->get($category.'_'.$id1.'_'.$id2)->getData() : null;
                        $data[$category][$id1][$id2] = $form->get($category.'_'.$id1.'_'.$id2)->getData();
                    }
                } else {
//                    $data[$category][$id1] = null || ' ' !== $form->get($category.'_'.$id1)->getData() ? $form->get($category.'_'.$id1)->getData() : null;
                    $data[$category][$id1] = $form->get($category.'_'.$id1)->getData();
                }
            }

            $catalogueTo[$category] = $data[$category];
//            dd($catalogueTo);

            $transCatalogue = new MessageCatalogue('', $catalogueTo);
            $dumper->dump($transCatalogue, [
                'resource' => $resourceTo,
                'translationDir' => $loader->getTranslationDirectory(),
            ]);

            $this->addFlash('success', 'Fordítások sikeresen elmentve!');
            return $this->redirectToRoute('translation-edit-byCategory', ['interface' => $interface, 'category' => $category]);
        }

        return $this->render('admin/translation/translation-edit.html.twig', [
            'interface' => $interface,
            'navigationTabs' => $tabs,
            'forms' => $forms,
            'sourceText' => $sourceText,
            'sourceLabel' => $sourceLabel,
//            'incompleteCount' => $incompleteCount,
            'incompleteCount' => $this->countMissing($catalogueTo),
        ]);
    }

    /**
     * @param string $needle        # The string we're searching for
     * @param array $haystack       # The multilevel array we're parsing
     * @return string|bool          # The key of the found string. Eg.: 'settings.translation.translation-title'
     *                              # Returns false if no result.
     */
    private function findKey(string $needle, array $haystack)
    {
        foreach($haystack as $key=>$value) {
            $current_key=$key;
//            if($needle===$value OR (!is_array($value) && strpos($value, $needle) !== false ) OR (is_array($value) && $this->find($needle,$value) !== false)) {
            if($needle===$value OR (!is_array($value) && strpos($value, $needle) !== false ) OR (is_array($value) && $this->findKey($needle,$value) !== false)) {
                if (is_array($value) && $this->findKey($needle,$value) !== false) {
                    return $current_key.'.'.$this->findKey($needle, $value);
                }
                return $current_key;
            }
        }
        return false;
    }

    /**
     * @param string $needle        # The string we're searching for. Eg.: 'azonos'
     * @param array $haystack       # The multilevel array we're parsing
     * @return string|bool          # The text in which the $needle was found. Eg.: 'Belső azonosító:
     *                              # Returns false if no result.
     */
    private function findValue(string $needle, array $haystack)
    {
        foreach($haystack as $key=>$value) {
            $current_value=$value;
//            if($needle===$value OR (!is_array($value) && strpos($value, $needle) !== false ) OR (is_array($value) && $this->find($needle,$value) !== false)) {
            if($needle===$value OR (!is_array($value) && strpos($value, $needle) !== false ) OR (is_array($value) && $this->findValue($needle,$value) !== false)) {
                if (is_array($value) && $this->findValue($needle,$value) !== false) {
                    return $this->findValue($needle, $value);
                }
                return $current_value;
            }
        }
        return false;
    }


    /**
     * @param array $catalogue      # For example: Admin catalogue
     * @return int
     */
    private function countMissing($catalogue = [])
    {
        $incompleteCount = 0;

        foreach ($catalogue as $key => $level1) {

            // $level1 = category level (Eg.: Order, Dashboard, Settings, Generic)
            if (is_array($level1)) {
                foreach ($level1 as $id1 => $level2) {

                    // $level2 = subcategory level (Eg.: Order >> History, Order >> User agent, Order >> Customer)
                    if (is_array($level2)) {
                        foreach ($level2 as $id2 => $level3) {
                            if ( $level3 === '' || $level3 === null ) {
                                $incompleteCount += 1;
                            }
                        }
                    } else {
                        if ( $level2 === '' || $level2 === null ) {
                            $incompleteCount += 1;
                        }
                    }
                }
            }
        }
        return $incompleteCount;
    }

    /**
     * @Route("/edit/", name="translation-edit")
     */
    public function editTranslation(Request $request, TranslatorInterface $translator, TranslationLoader $loader)
    {
        $category = $request->query->get('category');

        $catalogueFrom = $loader->load('messages.en.yaml');
        $catalogueTo = $loader->load('messages.hu.yaml');

        $catalogueFrom = $catalogueFrom['admin'];
        $catalogueTo = $catalogueTo['admin'];

        $huSite = $catalogueTo['site'];

        foreach ($catalogueTo as $key => $module) {
            $formBuilder = [$key => $this->createFormBuilder()];

            if (is_array($module)) {
                foreach ($module as $id => $translation) {
                    $label = ucfirst(str_replace("-", " ", $id));
                    $formBuilder[$key]->add($key.'_'.$id, TextType::class, [
                        'mapped' => false,
                        'required' => false,
//                        'label' => $label,
                        'label' => $id,
                        'data' => $translation,
                        'empty_data' => ' ',
                    ]);
                }
                $forms[$key] = $formBuilder[$key]->getForm()->createView();
//            dd($formBuilder[$key]->getForm());
            } else {
//                $formBuilder[$key]->add('')
            }

        }

        foreach ($catalogueFrom as $key => $module) {
            if (is_array($module)) {
                foreach ($module as $id => $translation) {
                    $label = ucfirst(str_replace("-", " ", $id));

                    $source[$key][$id] = $translation;
                }
            } else {
//                $formBuilder[$key]->add('')
            }
        }

        // Create navigation tabs
        foreach ($catalogueFrom as $key => $module) {
            $tabs[$key] = $this->generateUrl('translation-edit-byCategory', ['category' => $key]);
        }

        return $this->render('admin/translation/translation-edit.html.twig', [
            'forms' => $forms,
            'sourceText' => $source,
            'navigationTabs' => $tabs,
        ]);
    }
}