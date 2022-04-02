<?php

namespace App\Controller\Front;

use App\Entity\Trial;
use App\Entity\User;
use App\Repository\TrialRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/trial')]
class TrialController extends AbstractController
{
    #[Route('/', name: 'trial_index', methods: ['GET', 'POST'])]
    public function index(Request $request, TrialRepository $trialRepository): Response
    {
        if ($request->isMethod('POST') && !$this->isCsrfTokenValid('trialFilter', $request->request->get('_token'))) {
            $this->addFlash('red', "SecurityError");
            return $this->render('front/trial/index.html.twig', [
                'trials' => $trialRepository->findBy(["status" => "AWAITING", "tournament" => NULL], ["dateStart" => "ASC"]),
                'status' => "AWAITING"
            ]);
        }
        $status = $request->request->get('status') ?? "AWAITING";
        return $this->render('front/trial/index.html.twig', [
            'trials' => $trialRepository->findBy(["status" => $status, "tournament" => NULL], ["dateStart" => "ASC"]),
            'status' => $status
        ]);
    }

    // #[Route('/competitors',  name: 'trial_competitors', methods: ['GET'])]
    // public function competitors(Request $request, TrialRepository $trialRepository) 
    // {
    //     // User actuellement
    //     $userConnected = $this->get('security.token_storage')->getToken()->getUser();

    //     // SQL => get all users 
    //     $getUsers = $trialRepository->findFighters($userConnected->getId());
        
    //     // Filtrer tout les fighters 
    //     $getFighters = [];

    //     foreach($getUsers as $user){
    //        if( in_array('ROLE_FIGHTER',$user['roles']) ){
    //             $getFighters[] = $user;
    //        }
    //     } 
    //     return $this->render('front/trial/competitors.html.twig', [ 
    //         'fighters' => $getFighters
    //     ]);
    // }

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

    #[Route('/consult', name: 'trial_consult', methods: ['GET', 'POST'])]
    public function consult(Request $request, TrialRepository $trialRepository): Response
    {
        $user = $this->getUser();
        return $this->render('front/trial/consult.html.twig', [
            'nextTrials' => $trialRepository->findIncomingTrials($user),
            'nextChallenges' => $trialRepository->findIncomingChallenges($user),
            'trials' => $trialRepository->findNormalChallenges($user)
        ]);
    }

    #[Route('/accept/trial/{id}', name: 'trial_accept', methods: ['POST'])]
    public function acceptTrial(Request $request, Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('acceptTrial'.$trial->getId(), $request->request->get('_token'))) {
            if($trial->getStatus() == "CREATED"){
                $trial->setStatus("DATE_ACCEPTED");
            } else if($trial->getStatus() == "DATE_ACCEPTED"){
                $trial->setStatus("AWAITING");
            }
            $entityManager->flush();
        }
        return $this->redirectToRoute('front_trial_consult', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/accept/challenge/{id}', name: 'trial_challenge_accept', methods: ['POST'])]
    public function acceptChallenge(Request $request, Trial $trial, EntityManagerInterface $entityManager): Response
    {
        dd("faut taffer ici");
        // if ($this->isCsrfTokenValid('acceptChallenge'.$trial->getId(), $request->request->get('_token'))) {
        //     $trial->setStatus("ACCEPTED");
        //     $entityManager->flush();
        // }
        // return $this->redirectToRoute('front_trial_consult', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/refuse/date/{id}', name: 'trial_refuse_date', methods: ['POST'])]
    public function refuseDate(Request $request, Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('refuseDate'.$trial->getId(), $request->request->get('_token'))) {
            $trial->setStatus("DATE_REFUSED");
            $entityManager->flush();
            //SHOULD SEND EMAIL
        }
        return $this->redirectToRoute('front_trial_consult', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/refuse/{id}', name: 'trial_refuse', methods: ['POST'])]
    public function refuse(Request $request, Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('refuse'.$trial->getId(), $request->request->get('_token'))) {
            $trial->setStatus("REFUSED");
            $entityManager->flush();
            //SHOULD SEND EMAIL
        }
        return $this->redirectToRoute('front_trial_consult', [], Response::HTTP_SEE_OTHER);
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
