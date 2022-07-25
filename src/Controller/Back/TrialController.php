<?php

namespace App\Controller\Back;

use App\Entity\Trial;
use App\Entity\User;
use App\Form\TrialType;
use App\Security\Voter\TrialVoter;
use App\Repository\TrialRepository;
use App\Repository\UserRepository;
use App\Service\TrialService;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/trial')]
class TrialController extends AbstractController
{
    #[Route('/', name: 'trial_index', methods: ['GET'])]
    public function index(Request $request, TrialRepository $trialRepository): Response
    {
        $status = in_array($request->query->get('status'),Trial::ENUM_STATUS) ? $request->query->get('status') : "CREATED";
        return $this->render('back/trial/index.html.twig', [
            'trials' => $trialRepository->findBy(["status" => $status, "tournament" => NULL], ["dateStart" => "ASC"]),
            'status' => $status
        ]);
    }

    #[Route('/start/{id}', name: 'trial_start', methods: ['POST'])]
    #[IsGranted(TrialVoter::EDIT, "trial")]
    public function start(Request $request,Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if(!$this->isCsrfTokenValid('start'.$trial->getId(), $request->request->get('_token'))){
            $this->addFlash('danger', "SecurityError");
            return $this->redirectToRoute('back_trial_index', [], Response::HTTP_SEE_OTHER);
        }
        $trial->setBetStatus(0);
        $trial->setStatus("STARTED");
        $entityManager->flush();
        $this->addFlash('success', 'Trial started, don\'t forget to end it after your stream');
        return $this->redirectToRoute('back_trial_show', ["id" => $trial->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/end/{id}', name: 'trial_end', methods: ['POST'])]
    #[IsGranted(TrialVoter::EDIT, "trial")]
    public function end(Request $request,Trial $trial, EntityManagerInterface $entityManager, TrialService $ts): Response
    {
        if(!$this->isCsrfTokenValid('endTrial'.$trial->getId(), $request->request->get('_token'))){
            $this->addFlash('danger', "SecurityError");
            return $this->redirectToRoute('front_trial_show', ["id" => $trial->getId()], Response::HTTP_SEE_OTHER);
        }
        if(!$request->request->get('victoryType') || !$request->request->get('fighter')){
            $this->addFlash('danger', "Missing parameters, send confirm form");
            return $this->redirectToRoute('front_trial_show', ["id" => $trial->getId()], Response::HTTP_SEE_OTHER);
        }
        $ts->endTrial($trial, $entityManager->getRepository(User::class)->findOneBy(['id' => $request->request->get('fighter')]),$request->request->get('victoryType') );
        $this->addFlash('success', "Ranking modified and trial ended");
        return $this->redirectToRoute('back_default', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/accept/challenge/{id}', name: 'trial_accept_challenge', methods: ['POST'])]
    #[IsGranted(TrialVoter::CHALLENGE_ANSWER, "trial")]
    public function acceptChallenge(Request $request, Trial $trial,EntityManagerInterface $entityManager): Response
    {
        if(!$this->isCsrfTokenValid('acceptChallengeBack'.$trial->getId(), $request->request->get('_token'))){
            $this->addFlash('danger', "SecurityError");
            return $this->redirectToRoute('back_trial_index', [], Response::HTTP_SEE_OTHER);
        }
        $trial->setStatus("VALIDATED");
        $trial->setAdjudicate($this->getUser());
        $entityManager->flush();
        $this->addFlash('success', "Modify the date to continue process");
        return $this->redirectToRoute('back_trial_modify_date', ["id"=>$trial->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/refuse/challenge/{id}', name: 'trial_refuse_challenge', methods: ['POST'])]
    #[IsGranted(TrialVoter::CHALLENGE_ANSWER, "trial")]
    public function refuseChallenge(Request $request, Trial $trial,TrialRepository $trialRepository, EntityManagerInterface $entityManager): Response
    {
        if(!$this->isCsrfTokenValid('refuseChallengeBack'.$trial->getId(), $request->request->get('_token'))){
            $this->addFlash('danger', "SecurityError");
            return $this->redirectToRoute('back_trial_index', [], Response::HTTP_SEE_OTHER);
        }
        $trial->setStatus("REFUSED");
        $entityManager->flush();
        return $this->redirectToRoute('back_trial_index', ["status" => "REFUSED"], Response::HTTP_SEE_OTHER);
    }

    #[Route('/new', name: 'trial_new', methods: ['GET', 'POST'])]
    #[IsGranted(TrialVoter::CREATE)]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $fighters = $userRepository->findByRole("ROLE_FIGHTER");
        if ($request->isMethod('POST')) {
            if(!$this->isCsrfTokenValid('newTrial', $request->request->get('_token')) || !$request->request->get('fighter1') || !$request->request->get('fighter2') || !$request->request->get('dateStart') || !$request->request->get('timeStart')){
                $this->addFlash('danger', "SecurityError");
                return $this->render('back/trial/new.html.twig',[
                    'fighters' => $fighters
                ]);
            }
            $trial = new Trial();
            $trial->addFighter($userRepository->find($request->request->get('fighter1')));
            $trial->addFighter($userRepository->find($request->request->get('fighter2')));
            $trial->setAdjudicate($this->getUser());
            $trial->setDateStart(new \DateTime($request->request->get('dateStart')." ".$request->request->get('timeStart')));
            $entityManager->persist($trial);
            $entityManager->flush();

            return $this->redirectToRoute('back_trial_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/trial/new.html.twig',[
            'fighters' => $fighters
        ]);
    }

    #[Route('/{id}', name: 'trial_show', methods: ['GET'])]
    public function show(Trial $trial): Response
    {
        if($trial->getStatus() === "STARTED"){
            return $this->render('back/trial/handle.html.twig', [
                'trial' => $trial,
                'victoryTypes' => Trial::ENUM_VICTORY,
            ]);
        }
        return $this->render('back/trial/show.html.twig', [
            'trial' => $trial
        ]);
    }

    #[Route('/modifyDate/{id}', name: 'trial_modify_date', methods: ['GET', 'POST'])]
    #[IsGranted(TrialVoter::CREATE)]
    public function modifyDate(Request $request, Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            if($this->isCsrfTokenValid('modifyDate'.$trial->getId(), $request->request->get('_token'))){
                return $this->render('back/trial/edit.html.twig', [
                    'trial' => $trial,
                ]);
            }
            if(!$this->isCsrfTokenValid('editTrial'.$trial->getId(), $request->request->get('_token')) || !$request->request->get('dateStart') || !$request->request->get('timeStart')){
                $this->addFlash('danger', "SecurityError");
                return $this->render('back/trial/edit.html.twig', [
                    'trial' => $trial,
                ]);
            }
            if(!in_array($trial->getStatus(), ["DATE_REFUSED","VALIDATED"])){
                $this->addFlash('danger', "Trying to modify a conform trial");
                return $this->render('back/trial/edit.html.twig', [
                    'trial' => $trial,
                ]);
            }
            $dateStart = new \DateTime($request->request->get('dateStart')." ".$request->request->get('timeStart'), new DateTimeZone("Europe/Paris"));
            $trial->setDateStart($dateStart);
            $trial->setStatus("CREATED");
            $entityManager->flush();
            $this->addFlash('success', "trial modified");
            return $this->redirectToRoute('back_trial_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/trial/edit.html.twig', [
            'trial' => $trial,
        ]);
    }

    #[Route('/{id}', name: 'trial_delete', methods: ['POST'])]
    public function delete(Request $request, Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$trial->getId(), $request->request->get('_token'))) {
            $entityManager->remove($trial);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_trial_index', [], Response::HTTP_SEE_OTHER);
    }
}
