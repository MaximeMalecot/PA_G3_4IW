<?php

namespace App\Controller\Front;

use App\Entity\Trial;
use App\Repository\TrialRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/trial')]
class TrialController extends AbstractController
{
    #[Route('/', name: 'front_trial', methods: ['GET', 'POST'])]
    public function index(Request $request, TrialRepository $trialRepository): Response
    {
        if($request->isMethod('POST')){
            dd($request->request->get('filter-select'));
        }
        return $this->render('front/trial/index.html.twig', [
            'controller_name' => 'TrialController',
            'trials' => $trialRepository->findBy(["status" => "AWAITING", "tournament" => NULL], ["dateStart" => "ASC"])
        ]);
    }

    #[Route('/{id}',  name: 'front_trial_show', methods: ['GET'])]
    public function show(Trial $trial): Response 
    {
        if($trial->getStatus() === "STARTED"){
            return $this->render('front/trial/live.html.twig', [
                'controller_name' => 'TrialController',
                'trial' => $trial
            ]);
        }
        return $this->render('front/trial/show.html.twig', [
            'controller_name' => 'TrialController',
            'trial' => $trial
        ]);
    }
}
