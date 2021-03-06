<?php

namespace App\Controller\Front;

use App\Entity\Bet;
use App\Entity\User;
use App\Entity\Trial;
use App\Form\BetType;
use App\Entity\Tournament;
use App\Service\BetService;
use App\Service\TournamentService;
use App\Security\Voter\TournamentVoter;
use App\Repository\TournamentRepository;
use App\Repository\TrialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/tournament')]
class TournamentController extends AbstractController
{
    #[Route('/', name: 'tournament_index', methods: ['GET'])]
    public function index(Request $request, TournamentRepository $tournamentRepository): Response
    {
        if($this->getUser()){
            if ($this->isGranted("ROLE_FIGHTER")) {
                $status = in_array($request->query->get('status'),["STARTED", "AWAITING", "ENDED", "CREATED"]) ? $request->query->get('status') : "CREATED";
            }
            else {
                $status = in_array($request->query->get('status'),["STARTED", "AWAITING", "ENDED", "CREATED"]) ? $request->query->get('status') : "STARTED";
            }
        } else {
            $status = in_array($request->query->get('status'),["STARTED", "AWAITING", "ENDED"]) ? $request->query->get('status') : "STARTED";
        }
        return $this->render('front/tournament/index.html.twig', [
            'tournaments' => $tournamentRepository->findBy(["status" => $status ], ["dateStart" => "ASC"]),
            'status' => $status
        ]);
    }


    #[Route('/{id}/join', name: 'tournament_join', methods: ['POST'])]
    #[IsGranted(TournamentVoter::JOIN, 'tournament')]
    public function join(Request $request, Tournament $tournament, TournamentService $ts, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('join'.$tournament->getId(), $request->request->get('_token'))) {
            if($tournament->getStatus() === "AWAITING"){
                $ts->addToTrial($tournament, $this->getUser());
            }else{
                $tournament->addParticipant($this->getUser());
            }
            $em->flush();
            return $this->redirectToRoute('front_tournament_index', ['status' => "CREATED"], Response::HTTP_SEE_OTHER);
        }
        return $this->redirectToRoute('front_tournament_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/quit', name: 'tournament_quit', methods: ['POST'])]
    #[IsGranted(TournamentVoter::QUIT, 'tournament')]
    public function quit(Request $request, Tournament $tournament, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('quit'.$tournament->getId(), $request->request->get('_token'))) {
            $tournament->removeParticipant($this->getUser());
            $em->flush();
            return $this->redirectToRoute('front_tournament_index', ['status' => "CREATED"], Response::HTTP_SEE_OTHER);
        }
        return $this->redirectToRoute('front_tournament_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'tournament_show', methods: ['GET'])]
    public function show(Tournament $tournament,TrialRepository $trialRepository, EntityManagerInterface $em): Response 
    {
        if($tournament->getStatus() === "STARTED"){
            $startedTrial = $trialRepository->findBy(["tournament" => $tournament->getId(), "tournamentStep" => $tournament->getStep(), "status" => "STARTED"]);
            if(count($startedTrial) > 0){
                return $this->render('front/tournament/live.html.twig', [
                    'tournament' => $tournament,
                    'trials' => $em->getRepository(Trial::class)->findBy(['tournament' => $tournament, 'tournamentStep' => $tournament->getStep()], ['tournamentStep' => 'ASC']),
                    'dns'=> $_ENV['SERVER_DNS']
                ]);
            }
        }
        return $this->render('front/tournament/show.html.twig', [
            'tournament' => $tournament,
            'trials' => $em->getRepository(Trial::class)->findBy(['tournament' => $tournament], ['tournamentStep' => 'ASC'])
        ]);
    }

    #[Route('/{id}/bet', name: 'tournament_bet', methods: ['GET', 'POST'])]
    #[IsGranted(TournamentVoter::BET, 'tournament')]
    public function bet(Tournament $tournament, Request $request, BetService $betService): Response
    {
        $bet = new Bet();
        $form = $this->createForm(BetType::class, $bet, [
            'bet_type' => 'tournament',
            'entity' => $tournament,
        ]);
        $form->handleRequest($request);
        if (!$form->isSubmitted() && !$this->isCsrfTokenValid('bet'.$tournament->getId(), $request->request->get('_token'))) {
            $this->addFlash('red', "SecurityError");
            return $this->redirectToRoute('front_tournament_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $bet = $form->getData();
                try {
                    $betService->createBet($bet, tournament: $tournament);
                    $this->addFlash('success', 'Pari effectu?? avec succ??s');
                } catch (\Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            } else {
                $this->addFlash('red', $form->getErrors(false));
            }
            return $this->redirectToRoute('front_tournament_index', status: Response::HTTP_SEE_OTHER);
        }
        return $this->render('front/bet/create.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView(),
        ]);
    }
}
