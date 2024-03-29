<?php

declare(strict_types=1);

namespace App\Model;

use Error;

/**
 * This class creates properties like unscaled200, unscaled600, size600, sizeFacebook, productSmall, productMedium, etc.
 */
class ImageFileResource
{
    /**
     * @var array
     */
    private $data;

    public function createProperty(string $propertyName, $val){
        $this->data[$propertyName] = $val;

    }

    // magic methods!
    public function __set($property, $value){
        return $this->data[$property] = $value;
    }

    public function __get($property){
        return array_key_exists($property, $this->data)
            ? $this->data[$property]
            : null
            ;
    }

    public function setProperty($property, $value) {
        $this->__set($property, $value);
    }
    public function getProperty($property) {
        $this->__get($property);
    }

    // Eg: whenever I use a method like 'getUnscaled200()', the __call method bellow is called where $name parameter is 'getUnscaled200'
    public function __call($name, $args)
    {
        $property = lcfirst(substr($name, 3));

//        if (!array_key_exists($property, $this->data)) {
//            throw new \Error(sprintf('HIBA: Failed to find a property named %s', $property));
//        }

        if ('get' === substr($name, 0, 3)) {  //  if first 3 letters is 'get'
            if (!array_key_exists($property, $this->data)) {
                throw new Error(sprintf('HIBA: Failed to find a property named %s', $property));
            }
            return $this->data[$property] ?? null;
        } elseif ('set' === substr($name, 0, 3)) {
            if (!array_key_exists($property, $this->data)) {
                throw new Error(sprintf('HIBA: Failed to find a property named %s', $property));
            }
            $value = 1 == count($args) ? $args[0] : null;
            $this->data[$property] = $value;
        }
    }
}
