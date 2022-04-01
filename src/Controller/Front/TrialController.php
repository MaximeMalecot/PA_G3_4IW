<?php

namespace App\Controller\Front;

use App\Entity\Trial;
use App\Entity\User;
use App\Repository\TrialRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/trial')]
class TrialController extends AbstractController
{
    #[Route('/', name: 'trial_index', methods: ['GET', 'POST'])]
    public function index(Request $request, TrialRepository $trialRepository): Response
    {
        $status = $request->request->get('status') ?? "AWAITING";
        return $this->render('front/trial/index.html.twig', [
            'trials' => $trialRepository->findBy(["status" => $status, "tournament" => NULL], ["dateStart" => "ASC"]),
            'status' => $status
        ]);
    }

    #[Route('/competitors',  name: 'trial_competitors', methods: ['GET'])]
    public function competitors(Request $request, TrialRepository $trialRepository): Response 
    {
        // User actuellement
        $userConnected = $this->get('security.token_storage')->getToken()->getUser();

        // SQL => get all users 
        $getUsers = $trialRepository->findFighters($userConnected->getId());
        
        // Filtrer tout les fighters 
        $getFighters = [];

        foreach($getUsers as $user){
           if( in_array('ROLE_FIGHTER',$user['roles']) ){
                $getFighters[] = $user;
           }
        } 
        return $this->render('front/trial/competitors.html.twig', [ 
            'fighters' => $getFighters
        ]);
    }

    #[Route('/challenge/{id}',  name: 'trial_challenge', methods: ['GET'])]
    public function challenge(Request $request, TrialRepository $trialRepository,UserRepository $userRepository, int $id): Response 
    {

        $trial = new Trial();
        $getUser =  $userRepository->find($id);
        $userConnected = $this->get('security.token_storage')->getToken()->getUser();

        $trial->setCreatedBy($userConnected);
        $trial->setAcceptedBy($getUser);


        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($trial);
        $entityManager->flush();
            
        // SQL => get all users 
        $getUsers = $trialRepository->findFighters($userConnected->getId());
        
        // Filtrer tout les fighters 
        $getFighters = [];

        foreach($getUsers as $user){
           if( in_array('ROLE_FIGHTER',$user['roles']) ){
                $getFighters[] = $user;
           }
        } 
        return $this->render('front/trial/competitors.html.twig', [ 
            'fighters' => $getFighters
        ]);
    }

    #[Route('/{id}',  name: 'trial_show', methods: ['GET'])]
    public function show(Trial $trial): Response 
    {
        if($trial->getStatus() === "STARTED"){
            return $this->render('front/trial/live.html.twig', [
                'trial' => $trial
            ]);
        }
        return $this->render('front/trial/show.html.twig', [
            'trial' => $trial
        ]);
    }
}
