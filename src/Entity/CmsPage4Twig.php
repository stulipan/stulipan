<?php

namespace App\Entity;


/**
 * !!!!! NOT IN USE !!!!!!
 */
class CmsPage4Twig
{
    protected $properties = array();

    public function createProperty($property_name, $value)
    {
        $this->properties[$property_name] = $value;
    }

    public function __get($property_name)
    {
        if (isset($this->properties[$property_name])) {
            return $this->properties[$property_name];
        }
    }

    public function __call($name, $args)
    {
        $property_name = lcfirst(substr($name, 3));
        if ('get' === substr($name, 0, 3)) {
            return isset($this->properties[$property_name])
                ? $this->properties[$property_name]
                : null;
        } elseif ('set' === substr($name, 0, 3)) {
            $value = 1 == count($args) ? $args[0] : null;
            $this->properties[$property_name] = $value;
        }
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}