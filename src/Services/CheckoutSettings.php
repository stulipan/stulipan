<?php

namespace App\Services;

//use Error;
use Exception;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

/**
 * FYI:
 *      The class is also referenced in config\packages\twig.yaml
 *      Params '$storeSettingsDirectory' and '$checkoutSettingsFile' are defined in services.yaml
 */
final class CheckoutSettings
{
    private $parameters;
    private $settingsFile;

    public function __construct(string $storeSettingsDirectory, string $checkoutSettingsFile)
    {
        $configDirectories = [$storeSettingsDirectory];

        $fileLocator = new FileLocator($configDirectories);

        $this->settingsFile = $fileLocator->locate($checkoutSettingsFile, null, false);

        if (count($this->settingsFile) === 1) {
            $values = Yaml::parseFile($this->settingsFile[0]);
        } else {
            throw new Exception( sprintf('__HIBA__: Multiple %s files were found. Make sure you have only one %s file in your %s folder!', $checkoutSettingsFile, $storeSettingsDirectory));
        }

        $this->parameters = $values['parameters'];
    }

    /**
     * Eg: get('meta-title') ==> will return $this->parameters['meta-title']
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function get(string $name)
    {
        $strings = explode('.', $name);

        try {
            if ($this->parameters[$strings[0]][$strings[1]]['content'] !== null && $this->parameters[$strings[0]][$strings[1]]['content'] !== '' && $this->parameters[$strings[0]][$strings[1]]['content'] !== ' ') {
                return $this->parameters[$strings[0]][$strings[1]]['content'];
            }
            else {
                throw new Exception(sprintf('__HIBA__: %s::get() could not find the setting "%s" in file %s', (new ReflectionClass(get_class($this)))->getName(), $name, $this->settingsFile[0]));
            }
        } catch (Exception $e) {
            throw new Exception(sprintf('__HIBA__: %s::get() could not find the setting "%s" in file %s', (new ReflectionClass(get_class($this)))->getName(), $name, $this->settingsFile[0]));
        }
    }
}