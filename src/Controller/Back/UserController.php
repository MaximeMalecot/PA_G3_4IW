<?php

namespace App\Controller\Back;

use App\Entity\User;
use App\Form\UserPwdType;
use App\Form\UserBackType;
use App\Security\Voter\UserVoter;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('back/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    #[IsGranted(UserVoter::SHOW, 'user')]
    public function show(User $user): Response
    {
        return $this->render('back/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    #[IsGranted(UserVoter::EDIT, 'user')]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserBackType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('back_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
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

            return $this->redirectToRoute('back_user_edit', [
                'id' => $user->getId()
            ]);
        }

        return $this->render('back/user/edit.html.twig', [
            'user'=>$user,
            'form' => $form->createView()
         ]);
    }

    #[Route('/{id}', name: 'user_delete', methods: ['POST'])]
    #[IsGranted(UserVoter::DELETE, 'user')]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
