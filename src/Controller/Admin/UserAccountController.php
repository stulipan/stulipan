<?php

namespace App\Controller\Admin;

use App\Entity\Payment;
use App\Entity\Shipping;
use App\Entity\User;
use App\Form\ShippingFormType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/admin")
 */
class UserAccountController extends AbstractController
{
    /**
     * @var RoleHierarchyInterface
     */
    private $roleHierarchy;
    private $roles;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }


    /**
     * @Route("/user/show/{id}", name="user-showProfile")
     */
    public function showUserProfiler(User $user)
    {
        if (!$user) {
            throw $this->createNotFoundException('Nincs ilyen felhasználó!');
            //return $this->redirectToRoute('404');
        }

        return $this->render('admin/customer-profile-show.html.twig', [
            'user' => $user,
            'inheritedRoles' => $this->roles,
            'title' => 'Felhasználó adatlapja',
            'orderCount' => 'Nincs rendelés',
        ]);
    }


}