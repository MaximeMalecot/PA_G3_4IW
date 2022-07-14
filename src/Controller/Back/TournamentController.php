<?php

namespace App\Controller\Back;

use App\Entity\Tournament;
use App\Repository\UserRepository;
use App\Security\Voter\TournamentVoter;
use App\Repository\TournamentRepository;
use App\Service\TournamentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/tournament')]
class TournamentController extends AbstractController
{
    #[Route('/', name: 'tournament_index', methods: ['GET'])]
    public function index(Request $request, TournamentRepository $tournamentRepository): Response
    {
        $status = in_array($request->query->get('status'),Tournament::ENUM_STATUS) ? $request->query->get('status') : "AWAITING";
        return $this->render('back/tournament/index.html.twig', [
            'tournaments' => $tournamentRepository->findBy(["status" => $status], ["dateStart" => "ASC"]),
            'status' => $status
        ]);
    }

    #[Route('/new', name: 'tournament_new', methods: ['GET', 'POST'])]
    #[IsGranted(TournamentVoter::CREATE)]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            if(!$this->isCsrfTokenValid('newTournament', $request->request->get('_token')) || !$request->request->get('name') || !$request->request->get('nbMaxParticipants') || !$request->request->get('dateStart') || !$request->request->get('timeStart') || !$request->request->get('dateEnd')){
                $this->addFlash('red', "SecurityError");
                return $this->render('back/tournament/new.html.twig');
            }
            if(log($request->request->get('nbMaxParticipants'),2)%1 !== 0){
                $this->addFlash('red', "Set max participants number to a natural exp of 2 (2,4,8,16,32,64,128,256,512,1024)");
                return $this->render('back/tournament/new.html.twig');
            }
            /* ADD VERIF ON NUMBER */
            $tournament = new Tournament();
            $tournament->setName($request->request->get('name'));
            $tournament->addParticipant($this->getUser());
            $tournament->setNbMaxParticipants($request->request->get('nbMaxParticipants'));
            $tournament->setDateStart(new \DateTime($request->request->get('dateStart')." ".$request->request->get('timeStart')));
            $tournament->setDateEnd(new \DateTime($request->request->get('dateEnd')));
            $entityManager->persist($tournament);
            $entityManager->flush();

            return $this->redirectToRoute('back_tournament_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/tournament/new.html.twig');
    }

    #[Route('/{id}', name: 'tournament_show', methods: ['GET'])]
    #[IsGranted(TournamentVoter::EDIT, 'tournament')]
    public function show(Tournament $tournament): Response
    {
        return $this->render('back/tournament/show.html.twig', [
            'tournament' => $tournament,
        ]);
    }

    #[Route('/{id}/join', name: 'tournament_join', methods: ['POST'])]
    #[IsGranted(TournamentVoter::JOIN, 'tournament')]
    public function join(Request $request, Tournament $tournament, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('join'.$tournament->getId(), $request->request->get('_token'))) {
            $tournament->addParticipant($this->getUser());
            $em->flush();
            $this->addFlash('green', "Tournament joined");
            return $this->redirectToRoute('back_tournament_index', ['status' => "CREATED"], Response::HTTP_SEE_OTHER);
        }
        return $this->redirectToRoute('back_tournament_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/quit', name: 'tournament_quit', methods: ['POST'])]
    #[IsGranted(TournamentVoter::QUIT, 'tournament')]
    public function quit(Request $request, Tournament $tournament, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('quit'.$tournament->getId(), $request->request->get('_token'))) {
            $tournament->removeParticipant($this->getUser());
            $em->flush();
            $this->addFlash('green', "Tournament left");
            return $this->redirectToRoute('back_tournament_index', ['status' => "CREATED"], Response::HTTP_SEE_OTHER);
        }
        return $this->redirectToRoute('back_tournament_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/lock', name: 'tournament_lock', methods: ['POST'])]
    #[IsGranted(TournamentVoter::LOCK, 'tournament')]
    public function lock(Request $request, Tournament $tournament, TournamentService $ts, EntityManagerInterface $em): Response
    {
        if(count($tournament->getParticipantFromRole("ROLE_ADJUDICATE")) !== $tournament->getNbMaxParticipants() / 2 && 
            count($tournament->getParticipantFromRole("ROLE_FIGHTER")) <= ($tournament->getNbMaxParticipants() / 2 ))
        {
            $this->addFlash('red', "Missing participants");
            return $this->render('back/tournament/index.html.twig', [
                'tournaments' => $em->getRepository(Tournament::class)->findBy(["status" => "CREATED"], ["dateStart" => "ASC"]),
                'status' => "CREATED"
            ]);
        }
        if ($this->isCsrfTokenValid('lock'.$tournament->getId(), $request->request->get('_token'))) {
            $ts->createTrialsForTournament($tournament);
            $tournament->setStatus("AWAITING");
            $em->flush();
            $this->addFlash('green', "Tournament initialized");
            return $this->render('back/tournament/index.html.twig', [
                'tournaments' => $em->getRepository(Tournament::class)->findBy(["status" => "AWAITING"], ["dateStart" => "ASC"]),
                'status' => "AWAITING"
            ]);
        }
        $this->addFlash('red', "Security error");
        return $this->redirectToRoute('back_tournament_index', ['status' => "AWAITING"], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'tournament_delete', methods: ['POST'])]
    public function delete(Request $request, Tournament $tournament, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tournament->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tournament);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_tournament_index', [], Response::HTTP_SEE_OTHER);
    }
}
