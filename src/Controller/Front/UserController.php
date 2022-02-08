<?php

namespace App\Controller\Front;

use App\Repository\UserRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\Voter\UserVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\User;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'front_user', methods: ['GET'])]
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

    #[Route('/{id}', name: 'front_user_show', requirements: ['id' => '^\d+$'], methods: ['GET'])]
    #[IsGranted(UserVoter::SHOW, 'user')]
    public function show(User $user): Response
    {
        if(in_array('ROLE_FIGHTER', $user->getRoles())){
            return $this->render('front/user/show.html.twig', [
                'user' => $user,
                'stats'=> $user->getFightingStats(),
            ]);
        }else{
            return $this->render('front/user/show.html.twig', [
                'user' => $user,
            ]);
        }
        

    }
}
