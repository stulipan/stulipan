<?php

namespace App\Services;

use Error;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\Yaml\Yaml;

class TranslationDumper implements DumperInterface
{
    public function dump(MessageCatalogue $messages, $options = [])
    {
        $translationDir = $options['translationDir'];
        $resource = $options['resource'];



        $fileLocator = new FileLocator($translationDir);
        $locatedFiles = $fileLocator->locate($resource, null, false);
        try {
            $translationCatalog = Yaml::dump($messages->all(),4, 4);
        } catch (DumpException $e) {
            throw new Error( sprintf('HIBA! Unable to dump the YAML string: %s', $e->getMessage()));
        }

        file_put_contents($locatedFiles[0], $translationCatalog);
    }
}