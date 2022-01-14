<?php

namespace App\Controller\Front;

use App\Entity\Trial;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    public function index(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $fighters = [];
        $adjudicates = [];
        foreach($manager->getRepository(User::class)->findByRole("ROLE_FIGHTER") as $fighter){
            $fighters[] = $manager->getRepository(User::class)->find($fighter->getId());
        }
        foreach($manager->getRepository(User::class)->findByRole("ROLE_ADJUDICATE") as $adjudicate){
            $adjudicates[] = $manager->getRepository(User::class)->find($adjudicate->getId());
        }
        $object = (new Trial())
            ->addFighter($fighters[0])
            ->addFighter($fighters[1])
            ->setAdjudicate($adjudicates[0]);
        $manager->persist($object);
        $manager->flush();
        //$adjudicates = $manager->getRepository(User::class)->findByRole("ROLE_ADJUDICATE");
        return $this->render('front/default/index.html.twig', [
            'controller_name' => 'FRONT',
        ]);
    }
}
