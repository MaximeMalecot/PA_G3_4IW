<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\Voter\BetVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\User;
use App\Entity\Bet;


#[Route('/bet')]
class BetController extends AbstractController
{
    #[Route('/user/{id}', name: 'front_bet', methods: ['GET'])]
    #[IsGranted(BetVoter::SHOW, 'user')]
    public function index(User $user): Response
    {
        return $this->render('front/bet/index.html.twig', [
            'controller_name' => 'UserController',
            'bets' => $user->getBets()
        ]);

    }
}
