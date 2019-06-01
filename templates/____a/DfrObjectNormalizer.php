<?php

namespace App\Normalizer;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DfrObjectNormalizer extends ObjectNormalizer
{
//    protected $classMetadataFactory;
//    protected $defaultContext;
//    public function __construct(array $defaultContext = [])
//    {
//        $this->classMetadataFactory = new ClassMetadataFactory(
//            new AnnotationLoader(new AnnotationReader())
//        );
//        $defaultContext[] = ObjectNormalizer::DISABLE_TYPE_ENFORCEMENT;
//        $this->defaultContext = $defaultContext;
//
//        parent::__construct($this->classMetadataFactory, null, null, new PhpDocExtractor(), null, null, $this->defaultContext);
//    }
}