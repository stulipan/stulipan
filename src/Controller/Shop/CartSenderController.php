<?php

namespace App\Controller\Shop;

use App\Entity\Address;
use App\Entity\Geo\GeoCountry;
use App\Services\OrderBuilder;
use App\Entity\Sender;
use App\Form\SenderType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Sodium\add;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


//   !!! NOT IN USE !!!!
class CartSenderController extends AbstractController
{
    /**
     * @var OrderBuilder
     */
    private $orderBuilder;

    private $errorMessage = 'Unauthorized access: Request must come through XmlHttpRequest and user must be logged in!';
    public function __construct(OrderBuilder $orderBuilder)
    {
        $this->orderBuilder = $orderBuilder;
    }


}