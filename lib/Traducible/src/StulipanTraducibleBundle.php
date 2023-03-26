<?php

namespace Stulipan\Traducible;

use Stulipan\Traducible\DependencyInjection\StulipanTraducibleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use function dirname;

class StulipanTraducibleBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

//    /**
//    * Overridden to allow for the custom extension alias (stulipan_traducible_doi).
//    */
//    public function getContainerExtension()
//    {
//        if (null === $this->extension) {
//            $this->extension = new StulipanTraducibleExtension();
//        }
//        return $this->extension;
//    }
}