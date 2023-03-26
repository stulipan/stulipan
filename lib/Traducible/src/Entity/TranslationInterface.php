<?php

declare(strict_types=1);

namespace Stulipan\Traducible\Entity;

interface TranslationInterface
{
    public static function getTranslatableEntityClass(): string;

    public function setTranslatable(TraducibleInterface $translatable): void;

    public function getTranslatable(): TraducibleInterface;

    public function setLocale(string $locale): void;

    public function getLocale(): string;

    public function isEmpty(): bool;
}
