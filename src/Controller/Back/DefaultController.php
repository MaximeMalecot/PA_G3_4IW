<?php

namespace App\Controller\Back;

use App\Entity\Tournament;
use App\Repository\TicketRepository;
use App\Repository\TournamentRepository;
use App\Repository\TrialRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    public function index(TrialRepository $trialRepository, TournamentRepository $tournamentRepository, TicketRepository $ticketRepository): Response
    {
        $user = $this->getUser();
        if(in_array('ROLE_ADJUDICATE', $user->getRoles())){
            return $this->render('back/default/index.html.twig', [
                'trials' => $trialRepository->findBy(['adjudicate' => $user, 'status' => 'AWAITING'], ['dateStart' => 'ASC'], 5, 0),
                'tournaments' => $tournamentRepository->findAjudicatedTournaments($user)
            ]);
        }else{
            return $this->render('back/default/index.html.twig', [
                'tickets' => 'BACK',
            ]);
        }
    }
}