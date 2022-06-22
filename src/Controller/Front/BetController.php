<?php

namespace App\Controller\Front;

use App\Entity\Bet;
use App\Entity\User;
use App\Form\BetType;
use App\Security\Voter\BetVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer un Bet vide, qu'on remplira ensuite avec les données du formulaire
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
