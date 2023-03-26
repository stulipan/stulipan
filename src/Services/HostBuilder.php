<?php

namespace App\Services;


use App\Entity\Host;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

final class HostBuilder
{
    public const HOSTLIST = [
        'hu'=> 'www.stulipan.dfr',
        'en'=> 'www.stulipan.com'
    ];
    public const DATE_FORMAT_DEFAULT = [
        'hu' => 'Y-m-d',
        'en' => 'm/d/Y',
    ];
    public const TIME_FORMAT_DEFAULT = [
        'hu' => 'H:i',
        'en' => 'H:i',
    ];

    /**
     * @var Host[]|ArrayCollection|null
     */
    private $hosts;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->hosts = $entityManager->getRepository(Host::class)->findAll();
    }

    /**
     * @return array|null
     */
    public function getHosts(): ?array
    {
        return $this->hosts->getValues();
    }


}