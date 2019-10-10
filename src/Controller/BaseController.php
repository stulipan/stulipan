<?php

namespace App\Controller;
use App\Entity\Model\ErrorEntity;
use App\Entity\Product\ProductCategory;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseController extends AbstractController
{
    /** @var Serializer  */
    protected $serializer;

    public function __construct()
    {
        $classMetadataFactory = new ClassMetadataFactory(
            new AnnotationLoader(
                new AnnotationReader()
            )
        );
        $objNormalizer = new ObjectNormalizer(
            $classMetadataFactory,
            null,
            null,
//            new ReflectionExtractor(),
            new PhpDocExtractor(),
            null,
            null,
            [ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
        );
        $normalizer = [
            new ArrayDenormalizer(),
            $objNormalizer,
        ];
        $this->serializer = new Serializer($normalizer, [new JsonEncoder()]);
    }
    
    /**
     * !!! Ezt mar nem kell hasznalni !!! Hasznald a jsonNormalized, egyel lejebb.
     * @param mixed $data Usually an object you want to serialize
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function serializeIntoJsonResponse($data, $statusCode = 200, array $context = [])
    {
        $json = $this->get('serializer')
            ->serialize($data, 'json', $context);
        return new JsonResponse($json, $statusCode, [], true);
    }
    
    /**
     * Name: jsonObjNormalized
     * Output: returns a JSON after it was normalized, when entities (objects) are setup with @ Groups Annotation
     *
     * @param mixed $data Usually an object you want to serialize
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function jsonObjNormalized($data, $statusCode = 200, array $context = [])
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $objNormalizer = new ObjectNormalizer($classMetadataFactory,null,null,new PhpDocExtractor(),
            null,null,
            [
                ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
//                ObjectNormalizer::CIRCULAR_REFERENCE_HANDLER=>function ($object) {
//                    if ($object instanceof ProductCategory) { return ['id' => $object->getId(), 'name' => $object->getName()]; }
//                    return $object->getId();
//                }
            ]);
        $serializer = new Serializer([$objNormalizer], [new JsonEncoder()]);
        $json = $serializer->serialize($data, 'json', $context);
        return new JsonResponse($json, $statusCode, [], true);
    }
    
    /**
     * Name: jsonNormalized
     * Output: returns a JSON after it was normalized, because entities (objects) are implementing the JsonSerializable interface
     *
     * @param mixed $data Usually an object you want to serialize
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function jsonNormalized($data, $statusCode = 200, array $context = [])
    {
        $jsonNormalizer = new JsonSerializableNormalizer(
            null,
            null,
            array(JsonSerializableNormalizer::CIRCULAR_REFERENCE_HANDLER=>function ($object) {
                if ($object instanceof ProductCategory) {
                    return ['id' => $object->getId(), 'name' => $object->getName()];
                }
                return $object->getId();

            })
        );
        $serializer = new Serializer([$jsonNormalizer], [new JsonEncoder()]);
        $json = $serializer->serialize($data, 'json', $context);
        return new JsonResponse($json, $statusCode, [], true);
    }
    
    /**
     * Returns an associative array of validation errors
     *
     * @param mixed $object
     * @return array            Array of errors, empty otherwise
     */
    protected function getValidationErrors($object, ValidatorInterface $validator) //, $constraints = null
    {
        $violations = $validator->validate($object);
        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $error = new ErrorEntity($violation->getPropertyPath(), $violation->getMessage());
                $errors[] = $error;
            }
            return $errors;
        }
        return [];
    }
    
    /**
     * Returns an associative array of validation errors
     *
     * {
     *     'firstName': 'This value is required',
     *     'subForm': {
     *         'someField': 'Invalid value'
     *     }
     * }
     *
     * @param FormInterface $form
     * @return array|string
     */
    protected function getErrorsFromForm(FormInterface $form)
    {
        foreach ($form->getErrors() as $error) {
            // only supporting 1 error per field
            // and not supporting a "field" with errors, that has more
            // fields with errors below it
            return $error->getMessage();
        }
        $errors = array();
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childError = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childError;
                }
            }
        }
        return $errors;
    }
    
    public function toArray($data)
    {
        return is_array($data) ? $data : [$data];
    }

}