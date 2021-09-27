<?php

namespace App\Services;

use Cocur\Slugify\Slugify;

class SlugBuilder
{
    private $slugBuilder;

    public function __construct()
    {
        $this->slugBuilder = new Slugify(['rulesets' => Localization::SLUGIFY_RULES]);
    }

    /**
     * Returns the slug-version of the string.
     * Example: 'This is a product' --> 'this-is-a-product'
     *
     * @param string|null $string
     * @return string|null
     */
    public function slugify(?string $string): ?string
    {
        if ($string) {
            return $this->slugBuilder->slugify($string);
        }
        return null;
    }

    /**
     * Returns the camelCase-version of the slug.
     * Example: 'this-is-a-product' --> 'thisIsAProduct'
     *
     * @param string|null $slug
     * @return string|null
     */
    public function convertSlugToCamelCase(?string $slug): ?string
    {
        if ($slug) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $slug))));
        }
        return null;
    }

    /**
     * Returns the camelCase-version of the slug.
     * Example: 'this-is-a-product' --> 'this_is_a_product'
     *
     * @param string|null $slug
     * @return string|null
     */
    public function convertSlugToUnderscoreCase(?string $slug): ?string
    {
        if ($slug) {
            return str_replace('-', '_', $slug);
        }
        return null;
    }
}