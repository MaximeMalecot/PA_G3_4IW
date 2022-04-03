<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Trial;
use App\Form\UserPwdType;
use App\Form\UserEditType;
use Doctrine\DBAL\Exception;
use App\Security\Voter\UserVoter;
use App\Repository\UserRepository;
use App\Form\UserUpgradeFighterType;
use App\Repository\TicketRepository;
use App\Service\FightingStatsService;
use App\Form\UserUpgradeAdjudicateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class UserController extends AbstractController
{

    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $repository): Response
    {
        try {
            $fighters = $repository->findByRole("ROLE_FIGHTER");
        } catch (Exception $e) {
            return $this->render('front/user/index.html.twig', [
                'fighters' => "An error occurred."
            ]);
        }
        return $this->render('front/user/index.html.twig', [
            'fighters' => $fighters
        ]);
    }
    #[Route('/challenge/fighter/{id}',  name: 'user_challenge', methods: ['GET','POST'])]
    public function challenge(User $fighter, Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response 
    {
        if ($request->isMethod("POST") && $this->isCsrfTokenValid('challenge'.$fighter->getId(), $request->request->get('_token'))) {
            $trial = new Trial();
            $trial->addFighter($this->getUser());
            $trial->addFighter($fighter);
            $entityManager->persist($trial);
            $entityManager->flush();
            $this->addFlash('green', "Vous venez de challenge {$fighter->getNickname()}");
        } else {
            $this->addFlash('red', "Security error");
        }
        return $this->redirectToRoute('front_user_index');
    }


    #[Route('/{id}', name: 'user_show', requirements: ['id' => '^\d+$'], methods: ['GET'])]
    #[IsGranted(UserVoter::SHOW, 'user')]
    public function show(User $user): Response
    {
        return $this->render('front/user/show.html.twig', [
            'user' => $user,
            'stats'=> $user->getFightingStats()
        ]);
    }

    #[Route('/{id}/delete', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('front_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'user_edit', requirements: ['id' => '^\d+$'], methods: ['GET', 'POST'])]
    #[IsGranted(UserVoter::EDIT, 'user')]
    public function edit(User $user, Request $request): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('green', "Le user {$user->getNickname()} à bien été édité.");

            return $this->redirectToRoute('front_user_edit', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('front/user/edit.html.twig', [
            'user'=>$user,
            'form' => $form->createView()
         ]);
    }

    #[Route('/{id}/pwd', name: 'user_pwd_edit', requirements: ['id' => '^\d+$'], methods: ['GET', 'POST'])]
    #[IsGranted(UserVoter::EDIT, 'user')]
    public function pwdChange(User $user, UserPasswordHasherInterface $userPasswordHasherInterface, Request $request): Response
    {
        $form = $this->createForm(UserPwdType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasherInterface->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('green', "Le user {$user->getNickname()} à bien été édité.");

            return $this->redirectToRoute('front_user_edit', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('front/user/edit.html.twig', [
            'user'=>$user,
            'form' => $form->createView()
         ]);
    }

    #[Route('/{id}/upgrade', name: 'user_upgrade', requirements: ['id' => '^\d+$'], methods: ['GET', 'POST'])]
    #[IsGranted(UserVoter::UPGRADE, 'user')]
    public function upgrade(User $user, Request $request, TicketRepository $ticketRepository, FightingStatsService $fightingStatsService): Response
    {
        $ticket = $ticketRepository->findOneBy(["createdBy" => $user->getId(), "status"=> "ACCEPTED"]);
        if($ticket->getRoleWanted() == "Fighter"){
            $form = $this->createForm(UserUpgradeFighterType::class, $user);
        } else {
            $form = $this->createForm(UserUpgradeAdjudicateType::class, $user);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($ticket->getRoleWanted() == "Fighter"){
                $fightingStatsService->setNewFighter($user);
                $ticket->setStatus("ENDED");
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                return $this->redirectToRoute('front_default');
            } else {
                $user->setRoles(['ROLE_ADJUDICATE']);
                $ticket->setStatus("ENDED");
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                return $this->redirectToRoute('back_default');
            }
        }

        return $this->render('front/user/upgrade.html.twig', [
            'form' => $form->createView()
         ]);
    }
}
