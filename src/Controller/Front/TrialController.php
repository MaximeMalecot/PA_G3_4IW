<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrialController extends AbstractController
{
    #[Route('/trial', name: 'front_trial')]
    public function index(): Response
    {
        return $this->render('front/trial/index.html.twig', [
            'controller_name' => 'TrialController',
        ]);
    }
}