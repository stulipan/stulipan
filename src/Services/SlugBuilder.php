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

    /**
     * Generates a numbered postfix and appends it to a string.
     *      Example: 'terebes-5' --> 'terebes-6'
     *      Example: 'terebes'   --> 'terebes-1'
     *
     * @param string $searchFor
     * @param string $searchIn
     * @return string
     */
    public function numberedPostfix(string $searchFor, string $searchIn)
    {
        // search for 'terebes' in 'terebes-1' ==> results: '-1'
        $stringDiff = str_replace($searchFor, '', $searchIn);
        // remove '-' ==> results: '1'
        $stringDiff = ltrim($stringDiff, '-');
        if ('' !== $stringDiff) {
            $number = (int) $stringDiff;
            $number += 1;
            $postfix = (string) $number;
        } else {
            $postfix = '1';
        }
        return $postfix;
    }
}