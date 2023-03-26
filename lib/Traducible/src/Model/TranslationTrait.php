<?php

declare(strict_types=1);

namespace Stulipan\Traducible\Model;

use Stulipan\Traducible\Entity\TraducibleInterface;

trait TranslationTrait
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * Will be mapped to translatable entity by TranslatableSubscriber
     *
     * @var TraducibleInterface
     */
    protected $translatable;

    public static function getTranslatableEntityClass(): string
    {
        // By default, the translatable class has the same name but without the "Translation" suffix
//        return Strings::substring(static::class, 0, -11);
        return substr(static::class, 0, -11);

    }

    /**
     * Sets entity, that this translation should be mapped to.
     */
    public function setTranslatable(TraducibleInterface $translatable): void
    {
        $this->translatable = $translatable;
    }

    /**
     * Returns entity, that this translation is mapped to.
     */
    public function getTranslatable(): TraducibleInterface
    {
        return $this->translatable;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function isEmpty(): bool
    {
        foreach (get_object_vars($this) as $var => $value) {
            if (in_array($var, ['id', 'translatable', 'locale'], true)) {
                continue;
            }

            if (is_string($value) && strlen(trim($value)) > 0) {
                return false;
            }

            if (! empty($value)) {
                return false;
            }
        }

        return true;
    }
}