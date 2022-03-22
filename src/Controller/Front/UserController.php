<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\DBAL\Exception;
use App\Form\UserUpgradeType;
use App\Security\Voter\UserVoter;
use App\Repository\UserRepository;
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
                'controller_name' => 'UserController',
                'fighters' => "An error occurred."
            ]);
        }
        return $this->render('front/user/index.html.twig', [
            'controller_name' => 'UserController',
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
    public function edit(User $user, UserPasswordHasherInterface $userPasswordHasherInterface, Request $request): Response
    {
        $form = $this->createForm(UserType::class, $user);
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

            return $this->redirectToRoute('front_user_show', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('front/user/edit.html.twig', [
            'user'=>$user,
            'form' => $form->createView()
         ]);
    }

    #[Route('/upgrade', name: 'user_upgrade', methods: ['GET', 'POST'])]
    public function upgrade(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserUpgradeType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('green', "Le user {$user->getNickname()} à bien été édité.");

            return $this->redirectToRoute('front_user_show', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('front/user/edit.html.twig', [
            'user'=>$user,
            'form' => $form->createView()
         ]);
    }
}
