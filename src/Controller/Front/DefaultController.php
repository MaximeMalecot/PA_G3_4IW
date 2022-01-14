<?php

namespace App\Controller\Front;

use App\Entity\Tournament;
use App\Entity\User;
use App\Utils\UArray;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    public function index(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        /*$fighters = [];
        $adjudicates = [];
        foreach($manager->getRepository(User::class)->findByRole("ROLE_FIGHTER") as $fighter){
            $fighters[] = $manager->getRepository(User::class)->find($fighter->getId());
        }
        foreach($manager->getRepository(User::class)->findByRole("ROLE_ADJUDICATE") as $adjudicate){
            $adjudicates[] = $manager->getRepository(User::class)->find($adjudicate->getId());
        }
        $tournament = (new Tournament())
            ->setname("test")
            ->setNbParticipants(16);
        for($i=0; $i<16; $i++){
            $tournament->addParticipant(UArray::getRandomElem($fighters));
        }
        for($i=0; $i<8; $i++){
            $tournament->addParticipant(UArray::getRandomElem($adjudicates));
        }
        $manager->persist($tournament);
        $manager->flush();*/
        //$array = [1,2,3,4,5];
        //echo(count($array));
        /*print_r($array);
        echo '<br>'.($this->getRandomUser($array)).'<br>';
        print_r($array);*/
        $tournament=$manager->getRepository(Tournament::class)->find(6);
        $manager->getRepository(Tournament::class)->createTrialsForTournament($tournament);
        return $this->render('front/default/index.html.twig', [
            'controller_name' => 'FRONT',
        ]);
        
    }
    public function getRandomUser(array &$array){
        $index = rand(0, count($array) -1);
        $elem = $array[$index];
        unset($array[$index]);
        $array = array_values($array);
        return $elem;
    }
}