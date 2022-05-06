<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Security\Voter\BetVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/bet')]
class BetController extends AbstractController
{
    #[Route('/', name: 'bet_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('front/bet/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    // Create Crud for Bet
    #[Route('/create', name: 'bet_create', methods: ['GET', 'POST'])]
    public function create(): Response
    {
        return $this->render('front/bet/create.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/edit/{id}', name: 'bet_edit', methods: ['GET', 'PUT'])]
    public function edit(User $user): Response
    {
        return $this->render('front/bet/edit.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/delete/{id}', name: 'bet_delete', methods: ['DELETE'])]
    #[IsGranted(BetVoter::DELETE, subject: 'bet')]
    public function delete(User $user): Response
    {
        return $this->render('front/bet/delete.html.twig', [
            'user' => $user
        ]);
    }
}
