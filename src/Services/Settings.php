<?php

namespace App\Services;

use Error;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

final class Settings
{
    private $parameters;

    public function __construct()
    {
        $configDirectories = [__DIR__.'/../Config'];

        $fileLocator = new FileLocator($configDirectories);
        $locatedFile = $fileLocator->locate('settings.yaml', null, false);

        if (count($locatedFile) === 1) {
            $values = Yaml::parseFile($locatedFile[0]);
        } else {
            throw new Error( sprintf('HIBA: Multiple %s files were found. Make sure you have only one %s file in your /src/Config folder!', 'settings.yaml'));
        }

        $this->parameters = $values['parameters'];
    }

    /**
     * Eg: get('meta-title') ==> will return $this->parameters['meta-title']
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        $strings = explode('.', $name);
        try {
            if ($this->parameters[$strings[0]][$strings[1]]['content'] !== null && $this->parameters[$strings[0]][$strings[1]]['content'] !== '' && $this->parameters[$strings[0]][$strings[1]]['content'] !== ' ') {
                return $this->parameters[$strings[0]][$strings[1]]['content'];
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }

    }
}