<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Security\Voter\BetVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/bet')]
class BetController extends AbstractController
{
    #[Route('/user/{id}', name: 'bet_user', methods: ['GET'])]
    #[IsGranted(BetVoter::SHOW, 'user')]
    public function index(User $user): Response
    {
        return $this->render('front/bet/index.html.twig', [
            'user' => $user
        ]);

    }
}
