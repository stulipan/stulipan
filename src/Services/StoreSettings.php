<?php

namespace App\Services;

use Error;
use Exception;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * FYI:
 *      The class is also referenced in config\packages\twig.yaml
 *      Params '$storeSettingsDirectory' and '$generalSettingsFile' are defined in services.yaml
 */
final class StoreSettings
{
    public const VARIANT_VIEW_AS_DROPDOWN = 'dropdown';
    public const VARIANT_VIEW_AS_VARIANT_PICKER = 'variant-picker';

    private $parameters;
    private $session;
    private $convert;

    public function __construct(string $storeSettingsDirectory, string $generalSettingsFile,
                                SessionInterface $session, DateFormatConvert $convert)
    {
        $this->session = $session;
        $configDirectories = [$storeSettingsDirectory];

        $fileLocator = new FileLocator($configDirectories);
        $locatedFile = $fileLocator->locate($generalSettingsFile, null, false);

        if (count($locatedFile) === 1) {
            $values = Yaml::parseFile($locatedFile[0]);
        } else {
            throw new Error( sprintf('HIBA: Multiple %s files were found. Make sure you have only one %s file in your %s folder!', $generalSettingsFile, $storeSettingsDirectory));
        }

        $this->parameters = $values['parameters'];
        $this->convert = $convert;
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

    /**
     * @return string
     */
    public function getDateFormat(): string
    {
        $name = 'general.date-format';
        $strings = explode('.', $name);
        try {
            if ($this->parameters[$strings[0]][$strings[1]]['content'] !== null && $this->parameters[$strings[0]][$strings[1]]['content'] !== '' && $this->parameters[$strings[0]][$strings[1]]['content'] !== ' ') {
                return $this->parameters[$strings[0]][$strings[1]]['content'];
            }
        } catch (Exception $e) {
            // Defaults to 'Y-m-d' if 'general.date-format' doesn't exist in the settings yaml file
            return Localization::DATE_FORMAT_DEFAULT[$this->session->get('_locale')];
        }
    }
    /**
     * @return string
     */
    public function getDateFormatInMomentJsFormat(): string
    {
        $name = 'general.date-format';
        $strings = explode('.', $name);
        try {
            if ($this->parameters[$strings[0]][$strings[1]]['content'] !== null && $this->parameters[$strings[0]][$strings[1]]['content'] !== '' && $this->parameters[$strings[0]][$strings[1]]['content'] !== ' ') {
                return $this->convert->convertDateFormatFromPhpToMomentJs($this->parameters[$strings[0]][$strings[1]]['content']);
            }
        } catch (Exception $e) {
            // Defaults to 'Y-m-d' if 'general.date-format' doesn't exist in the settings yaml file
            return $this->convert->convertDateFormatFromPhpToMomentJs(Localization::DATE_FORMAT_DEFAULT[$this->session->get('_locale')]);
        }
    }

    /**
     * @return string
     */
    public function getTimeFormat(): string
    {
        $name = 'general.time-format';
        $strings = explode('.', $name);
        try {
            if ($this->parameters[$strings[0]][$strings[1]]['content'] !== null && $this->parameters[$strings[0]][$strings[1]]['content'] !== '' && $this->parameters[$strings[0]][$strings[1]]['content'] !== ' ') {
                return $this->parameters[$strings[0]][$strings[1]]['content'];
            }
        } catch (Exception $e) {
            // Defaults to 'Y-m-d' if 'general.date-format' doesn't exist in the settings yaml file
            return Localization::TIME_FORMAT_DEFAULT[$this->session->get('_locale')];
        }
    }

    /**
     * @return bool
     */
    public function isFlowerShop(): bool
    {
        $name = 'general.flower-shop-mode';
        $strings = explode('.', $name);
        try {
            if ($this->parameters[$strings[0]][$strings[1]]['content'] !== null && $this->parameters[$strings[0]][$strings[1]]['content'] !== '' && $this->parameters[$strings[0]][$strings[1]]['content'] !== ' ') {
                return $this->parameters[$strings[0]][$strings[1]]['content'];
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}