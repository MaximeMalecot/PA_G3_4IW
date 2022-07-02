<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Tournament;
use App\Entity\Trial;
use App\Security\Voter\TournamentVoter;
use App\Repository\TournamentRepository;
use App\Service\TournamentService;
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
            $status = in_array($request->query->get('status'),["STARTED", "AWAITING", "ENDED", "CREATED"]) ? $request->query->get('status') : "AWAITING";
        } else {
            $status = in_array($request->query->get('status'),["STARTED", "AWAITING", "ENDED"]) ? $request->query->get('status') : "AWAITING";
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
    public function show(Tournament $tournament): Response 
    {
        return $this->render('front/tournament/show.html.twig', [
            'tournament' => $tournament
        ]);
    }
}
