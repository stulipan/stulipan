<?php

namespace App\Normalizer;



/// NINCS HASZNALVA /////
class MyCircularReferenceHandler
{
    public function __invoke($object)
    {
        return $object->id;
    }
}