<?php

namespace App\Controller\Front;

use App\Entity\Trial;
use App\Repository\TrialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/trial')]
class TrialController extends AbstractController
{
    #[Route('/', name: 'front_trial')]
    public function index(TrialRepository $trialRepository): Response
    {
        return $this->render('front/trial/index.html.twig', [
            'controller_name' => 'TrialController',
            'trials' => $trialRepository->findBy(["status" => "AWAITING", "tournament" => NULL], ["dateStart" => "ASC"])
        ]);
    }

    #[Route('/{id}',  name: 'front_trial_show', methods: ['GET'])]
    public function show(Trial $trial): Response 
    {
        return $this->render('front/trial/show.html.twig', [
            'controller_name' => 'TrialController',
            'tournament' => $trial
        ]);
    }
}
