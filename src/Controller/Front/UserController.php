<?php

namespace App\Controller\Front;

use App\Entity\User;
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

    #[Route('/{id}', name: 'user_show', requirements: ['id' => '^\d+$'], methods: ['GET'])]
    #[IsGranted(UserVoter::SHOW, 'user')]
    public function show(User $user): Response
    {
        return $this->render('front/user/show.html.twig', [
            'user' => $user,
            'stats'=> $user->getFightingStats()
        ]);
    }

    #[Route('/edit/{id}', name: 'user_edit', requirements: ['id' => '^\d+$'], methods: ['GET', 'POST'])]
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

    #[Route('/edit/pwd/{id}', name: 'user_pwd_edit', requirements: ['id' => '^\d+$'], methods: ['GET', 'POST'])]
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

    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('front_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/upgrade', name: 'user_upgrade', methods: ['GET', 'POST'])]
    public function upgrade(Request $request, TicketRepository $ticketRepository, FightingStatsService $fightingStatsService): Response
    {
        $user = $this->getUser();
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
            } else {
                $user->setRoles(['ROLE_ADJUDICATE']);
            }
            $ticket->setStatus("ENDED");
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('green', "Le user {$user->getNickname()} à bien été édité.");

            return $this->redirectToRoute('front_default');
        }

        return $this->render('front/user/edit.html.twig', [
            'user'=>$user,
            'form' => $form->createView()
         ]);
    }
}
