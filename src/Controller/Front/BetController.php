<?php

namespace App\Controller\Front;

use App\Entity\Bet;
use App\Form\BetType;
use App\Repository\TrialRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    // Create Crud for Bet
    #[Route('/create', name: 'bet_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TrialRepository $repository): Response
    {
        // TODO:
        // - Trouver un moyen de donner le choix à l'utilisateur de choisir entre les trials de tournaments et les trials classiques

        $form = $this->createForm(BetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $user = $this->getUser();
            $enteredAmount = $request->request->get('bet')['amount'];
            $userAmount = $user->getCredits();
            if ($form->isValid() && $enteredAmount > 0 && $enteredAmount <=$userAmount) {
                $trialId = $request->request->get('bet')['trial'];
                $trial = $repository->find($trialId);
                if ($trial) {
                    $alreadyExistingBet = $user->getBets()->filter(function ($bet) use ($trial) {
                        return $bet->getTrial()->getId() == $trial->getId();
                    });
                    if ($alreadyExistingBet->count()===0) {
                        $bet = new Bet();
                        $bet = $form->getData();
                        $bet->setBetter($user);
                        $bet->setTrial($trial);
                        $entityManager->persist($bet);
                        $entityManager->flush();
                        $user->setCredits($userAmount - $enteredAmount);
                        $entityManager->persist($user);
                        $entityManager->flush();

                        $this->addFlash('green', "Votre pari a bien été créé !");
                    } else {
                        $this->addFlash('red', 'Vous avez déjà mis un pari sur ce trial.');
                    }
                }
            } else {
                $this->addFlash('red', "Les données envoyées ne sont pas correctes. Veuillez réessayer.");
            }
            return $this->redirectToRoute('front_bet_create');
        }
        return $this->render('front/bet/create.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView()
        ]);
    }

}
