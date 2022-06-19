<?php

namespace App\Controller\Back;

use App\Entity\Tournament;
use App\Security\Voter\TrialVoter;
use App\Repository\TournamentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/tournament')]
class TournamentController extends AbstractController
{
    #[Route('/', name: 'tournament_index', methods: ['GET', 'POST'])]
    public function index(Request $request, TournamentRepository $tournamentRepository): Response
    {
        if ($request->isMethod('POST') && !$this->isCsrfTokenValid('tournamentFilter', $request->request->get('_token'))) {
            $this->addFlash('red', "SecurityError");
            return $this->render('back/tournament/index.html.twig', [
                'tournaments' => $tournamentRepository->findBy(["status" => "AWAITING"], ["dateStart" => "ASC"]),
                'status' => "AWAITING"
            ]);
        }
        $status = $request->request->get('status') ?? "AWAITING";
        return $this->render('back/tournament/index.html.twig', [
            'trials' => $tournamentRepository->findBy(["status" => $status], ["dateStart" => "ASC"]),
            'status' => $status
        ]);
    }

    #[Route('/new', name: 'tournament_new', methods: ['GET', 'POST'])]
    #[IsGranted(TrialVoter::CREATE)]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $fighters = $userRepository->findByRole("ROLE_FIGHTER");
        if ($request->isMethod('POST')) {
            if(!$this->isCsrfTokenValid('newTrial', $request->request->get('_token')) || !$request->request->get('fighter1') || !$request->request->get('fighter2') || !$request->request->get('dateStart') || !$request->request->get('timeStart')){
                $this->addFlash('red', "SecurityError");
                return $this->renderForm('back/tournament/new.html.twig',[
                    'fighters' => $fighters
                ]);
            }
            $tournament = new Tournament();
            $tournament->setAdjudicate($this->getUser());
            $tournament->setDateStart(new \DateTime($request->request->get('dateStart')." ".$request->request->get('timeStart')));
            $entityManager->persist($tournament);
            $entityManager->flush();

            return $this->redirectToRoute('back_tournament_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/tournament/new.html.twig',[
            'fighters' => $fighters
        ]);
    }

    #[Route('/{id}', name: 'tournament_show', methods: ['GET'])]
    public function show(Tournament $tournament): Response
    {
        return $this->render('back/tournament/show.html.twig', [
            'tournament' => $tournament,
        ]);
    }

    #[Route('/{id}', name: 'tournament_delete', methods: ['POST'])]
    public function delete(Request $request, Tournament $tournament, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tournament->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tournament);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_tournament_index', [], Response::HTTP_SEE_OTHER);
    }
}
