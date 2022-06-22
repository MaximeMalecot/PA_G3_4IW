<?php

namespace App\Controller\Front;

use App\Entity\Bet;
use App\Form\BetType;
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
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO:
        // - Trouver un moyen de donner le choix à l'utilisateur de choisir entre les trials de tournaments et les trials classiques
        // - Choisir entre retirer le montant de crédits de l'utilisateur dès le bet ou de le faire après le match

        $form = $this->createForm(BetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $user = $this->getUser();
            $enteredAmount = $request->request->get('bet')['amount'];
            $userAmount = $user->getCredits();
            if ($form->isValid() && $enteredAmount > 0 && $enteredAmount <=$userAmount) {
                $bet = new Bet();
                $bet = $form->getData();
                $bet->setBetter($user);
                $user->setCredits($userAmount - $enteredAmount);
                $entityManager->persist($bet);
                $entityManager->flush();

                $this->addFlash('green', "Votre pari a bien été créé !");
                return $this->redirectToRoute('front_bet_index');
            }
            $this->addFlash('red', "Les données envoyées ne sont pas correctes. Veuillez réessayer.");
            return $this->redirectToRoute('front_bet_create');
        }
        return $this->render('front/bet/create.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView()
        ]);
    }

}
