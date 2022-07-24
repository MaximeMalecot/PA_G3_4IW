<?php

namespace App\Controller\Front;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    public function index(): Response
    {

        if ($this->getUser()){
            return $this->redirectToRoute('user_edit', ["id" => $this->getUser()->getId()]);
        }
        return $this->render('front/default/index.html.twig', [
            'controller_name' => 'FRONT',
        ]);
        
    }
}