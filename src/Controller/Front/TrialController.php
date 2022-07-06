<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Trial;
use App\Repository\UserRepository;
use App\Security\Voter\TrialVoter;
use App\Repository\TrialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/trial')]
class TrialController extends AbstractController
{
    #[Route('/', name: 'trial_index', methods: ['GET', 'POST'])]
    public function index(Request $request, TrialRepository $trialRepository): Response
    {
        $status = in_array($request->query->get('status'),["AWAITING","STARTED","ENDED"]) ? $request->query->get('status') : "AWAITING";
        return $this->render('front/trial/index.html.twig', [
            'trials' => $trialRepository->findBy(["status" => $status, "tournament" => NULL], ["dateStart" => "ASC"]),
            'status' => $status
        ]);
    }

    #[Route('/consult', name: 'trial_consult', methods: ['GET', 'POST'])]
    #[IsGranted(TrialVoter::CONSULT)]
    public function consult(Request $request, TrialRepository $trialRepository): Response
    {
        $user = $this->getUser();
        return $this->render('front/trial/consult.html.twig', [
            'nextTrials' => $trialRepository->findIncomingTrials($user),
            'nextChallenges' => $trialRepository->findIncomingChallenges($user),
            'trials' => $trialRepository->findNormalChallenges($user)
        ]);
    }

    #[Route('/accept/challenge/{id}', name: 'trial_challenge_accept', methods: ['POST'])]
    #[IsGranted(TrialVoter::CHALLENGE_ANSWER, "trial")]
    public function acceptChallenge(Request $request, Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('acceptChallenge'.$trial->getId(), $request->request->get('_token'))) {
            $trial->setStatus("ACCEPTED");
            $entityManager->flush();
        }
        return $this->redirectToRoute('front_trial_consult', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/refuse/challenge/{id}', name: 'trial_challenge_refuse', methods: ['POST'])]
    #[IsGranted(TrialVoter::CHALLENGE_ANSWER, "trial")]
    public function refuseChallenge(Request $request, Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('refuseChallenge'.$trial->getId(), $request->request->get('_token'))) {
            $trial->setStatus("REFUSED");
            $entityManager->flush();
        }
        return $this->redirectToRoute('front_trial_consult', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/refuse/date/{id}', name: 'trial_refuse_date', methods: ['POST'])]
    #[IsGranted(TrialVoter::CHALLENGE_ANSWER, "trial")]
    public function refuseDate(Request $request, Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('refuseDate'.$trial->getId(), $request->request->get('_token'))) {
            $trial->setStatus("DATE_REFUSED");
            $entityManager->flush();
        }
        return $this->redirectToRoute('front_trial_consult', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/accept/trial/{id}', name: 'trial_accept', methods: ['POST'])]
    #[IsGranted(TrialVoter::TRIAL_ANSWER, "trial")]
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

    #[Route('/refuse/{id}', name: 'trial_refuse', methods: ['POST'])]
    #[IsGranted(TrialVoter::TRIAL_ANSWER, "trial")]
    public function refuse(Request $request, Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('refuse'.$trial->getId(), $request->request->get('_token'))) {
            $trial->setStatus("REFUSED");
            $entityManager->flush();
        }
        return $this->redirectToRoute('front_trial_consult', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}',  name: 'trial_show', methods: ['GET'])]
    public function show(Trial $trial): Response 
    {
        if($trial->getStatus() === "STARTED"){
            return $this->render('front/trial/live.html.twig', [
                'trial' => $trial,
                'victoryTypes' => Trial::ENUM_VICTORY,
            ]);
        }
        return $this->render('front/trial/show.html.twig', [
            'trial' => $trial
        ]);
    }

}
