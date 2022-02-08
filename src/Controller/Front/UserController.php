<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'front_user')]
    public function index(): Response
    {
        return $this->render('front/user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
