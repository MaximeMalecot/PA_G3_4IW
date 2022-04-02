<?php

namespace App\Controller\Back;

use App\Entity\Trial;
use App\Form\TrialType;
use App\Security\Voter\TrialVoter;
use App\Repository\TrialRepository;
use App\Repository\UserRepository;
use DateTime;
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
        if ($request->isMethod('POST') && !$this->isCsrfTokenValid('trialFilter', $request->request->get('_token'))) {
            $this->addFlash('red', "SecurityError");
            return $this->render('back/trial/index.html.twig', [
                'trials' => $trialRepository->findBy(["status" => "AWAITING", "tournament" => NULL], ["dateStart" => "ASC"]),
                'status' => "AWAITING"
            ]);
        }
        $status = $request->request->get('status') ?? "AWAITING";
        return $this->render('back/trial/index.html.twig', [
            'trials' => $trialRepository->findBy(["status" => $status, "tournament" => NULL], ["dateStart" => "ASC"]),
            'status' => $status
        ]);
    }

    #[Route('/new', name: 'trial_new', methods: ['GET', 'POST'])]
    #[IsGranted(TrialVoter::CREATE)]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $fighters = $userRepository->findByRole("ROLE_FIGHTER");
        if ($request->isMethod('POST')) {
            if(!$this->isCsrfTokenValid('newTrial', $request->request->get('_token')) || !$request->request->get('fighter1') || !$request->request->get('fighter2') || !$request->request->get('dateStart') || !$request->request->get('timeStart')){
                $this->addFlash('red', "SecurityError");
                return $this->renderForm('back/trial/new.html.twig',[
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

        return $this->renderForm('back/trial/new.html.twig',[
            'fighters' => $fighters
        ]);
    }

    #[Route('/{id}', name: 'trial_show', methods: ['GET'])]
    public function show(Trial $trial): Response
    {
        return $this->render('back/trial/show.html.twig', [
            'trial' => $trial,
        ]);
    }

    #[Route('/edit/{id}', name: 'trial_modify_date', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        dd('ahi');
    }

    #[Route('/modifyDate/{id}', name: 'trial_modify_date', methods: ['GET', 'POST'])]
    #[IsGranted(TrialVoter::CREATE)]
    public function modifyDate(Request $request, Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            if(!$this->isCsrfTokenValid('editTrial'.$trial->getId(), $request->request->get('_token')) || !$request->request->get('dateStart') || !$request->request->get('timeStart')){
                $this->addFlash('red', "SecurityError");
                return $this->render('back/trial/edit.html.twig', [
                    'trial' => $trial,
                ]);
            }
            if($trial->getStatus() !== "DATE_REFUSED"){
                $this->addFlash('red', "Trying to modify a conform trial");
                return $this->render('back/trial/edit.html.twig', [
                    'trial' => $trial,
                ]);
            }
            $trial->setDateStart(new \DateTime($request->request->get('dateStart')." ".$request->request->get('timeStart')));
            $trial->setStatus("CREATED");
            $entityManager->flush();
            $this->addFlash('green', "trial modified");
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
