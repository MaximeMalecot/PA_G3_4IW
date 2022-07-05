<?php

namespace App\Controller\Front;

use App\Entity\Bet;
use App\Entity\Tournament;
use App\Entity\Trial;
use App\Form\BetType;
use App\Service\BetService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

    #[Route('/tournament/{id}/create', name: 'bet_tournament_create', methods: ['GET', 'POST'])]
    #[ParamConverter('tournament', class: Tournament::class)]
    public function createBetOnTournament(Tournament $tournament, Request $request, BetService $betService): Response
    {
        if ($tournament->getStatus() !== "AWAITING") {
            $this->addFlash('danger', 'Ce tournoi est déjà terminé ou pas encore prêt pour recevoir des paris.');
        }
        $bet = new Bet();
        $form = $this->createForm(BetType::class, $bet, [
            'bet_type' => 'tournament',
            'entity' => $tournament,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $bet = $form->getData();
                try {
                    $betService->createBet($bet, tournament: $tournament);
                    $this->addFlash('success', 'Pari effectué avec succès');
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            } else {
                $this->addFlash('red', $form->getErrors(false));
            }
            return $this->redirectToRoute('front_trial_index', status: Response::HTTP_SEE_OTHER);
        }
        return $this->render('front/bet/create.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/trial/{id}/create', name: 'bet_trial_create', methods: ['GET', 'POST'])]
    #[ParamConverter('trial', class: Trial::class)]
    public function createBetOnTrial(Trial $trial, Request $request, BetService $betService): RedirectResponse|Response
    {
        if ($trial->getStatus() !== "AWAITING") {
            $this->addFlash('danger', 'Ce match est déjà terminé ou pas encore prêt pour recevoir des paris.');
            return $this->redirectToRoute('front_trial_index', status: Response::HTTP_SEE_OTHER);
        }
        $bet = new Bet();
        $form = $this->createForm(BetType::class, $bet, [
            'bet_type' => 'trial',
            'entity' => $trial
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $bet = $form->getData();
                dump($bet);
                try {
                    $betService->createBet($bet, trial: $trial);
                    $this->addFlash('success', 'Pari effectué avec succès');
                } catch (Exception $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            } else {
                $this->addFlash('red', $form->getErrors(false));
            }
            return $this->redirectToRoute('front_trial_index', status: Response::HTTP_SEE_OTHER);
        }
        return $this->render('front/bet/create.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView()
        ]);
    }


}
