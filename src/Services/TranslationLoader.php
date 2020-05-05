<?php

namespace App\Services;

use Error;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class TranslationLoader implements LoaderInterface
{
    private $directory;
    private $path;
    private $paths;

    public function __construct()
    {
        $this->path = __DIR__.'/../../translations';
        $this->paths = [
            $this->path,
//            __DIR__.'/../../translations',
//            __DIR__.'/../../translations/store',
//            __DIR__.'/../../translations/admin',
        ];
    }

    public function load($resource, string $type = null)
    {
        $fileLocator = new FileLocator($this->paths);

        $locatedFiles = $fileLocator->locate($resource, null, false);

        try {
            $translations = Yaml::parse(file_get_contents($locatedFiles[0]));
        } catch (ParseException $e) {
            throw new Error( sprintf('HIBA! Unable to parse the YAML string in file %s. Error message: %s', $locatedFiles[0], $e->getMessage()));
        }

//        $translations = json_decode(json_encode($translations));  // decodes it to stdClass!
        return $translations;
    }

    public function supports($resource, string $type = null)
    {

    }

    public function getResolver()
    {

    }

    public function setResolver(LoaderResolverInterface $resolver)
    {

    }

    public function setDirectory(string $dir)
    {
        $this->paths = [$this->path.'/'.$dir];
    }
    /**
     * Sajat helper funkciom
     *
     * @return array        # !!! It must be an array. Because of dump() in TranslationDumper class.
     */
    public function getTranslationDirectory ()
    {
        return $this->paths;
    }
}