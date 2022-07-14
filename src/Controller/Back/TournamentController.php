<?php

namespace App\Controller\Back;

use App\Entity\Trial;
use App\Entity\Tournament;
use App\Service\TrialService;
use App\Repository\UserRepository;
use App\Security\Voter\TrialVoter;
use App\Service\TournamentService;
use App\Repository\TrialRepository;
use App\Security\Voter\TournamentVoter;
use App\Repository\TournamentRepository;
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
            if($request->request->get('nbMaxParticipants') <= 2){
                $this->addFlash('red', "Min number is 2, number must be pow of 2 (ex: 4 or 8)");
                return $this->render('back/tournament/new.html.twig');

            }
            if(is_float(log($request->request->get('nbMaxParticipants'),2))){
                $log = log($request->request->get('nbMaxParticipants'),2);
                $int = floor($log);
                $decimals = $log - $int;
                if($decimals != 0){
                    $this->addFlash('red', "Set max participants number to a natural exp of 2 (2,4,8,16,32,64,128,256,512,1024)");
                    return $this->render('back/tournament/new.html.twig');
                }
            }
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
    #[IsGranted(TournamentVoter::SHOW, 'tournament')]
    public function show(Tournament $tournament, TrialRepository $trialRepository, EntityManagerInterface $em): Response
    {
        if($tournament->getStatus() === "STARTED" && $tournament->getParticipants()->contains($this->getUser()))
        {
            $startTrial = $trialRepository->findOneBy(["tournament" => $tournament->getId(), "adjudicate" => $this->getUser()->getId(), "tournamentStep" => $tournament->getStep(), "status" => "AWAITING"]);
            if($startTrial){
                return $this->render('back/tournament/handle.html.twig', [
                    'tournament' => $tournament,
                    'trials' => $em->getRepository(Trial::class)->findBy(['tournament' => $tournament], ['tournamentStep' => 'ASC']),
                    'startTrial' => $startTrial,
                ]);
            }
            $endTrial = $trialRepository->findOneBy(["tournament" => $tournament->getId(), "adjudicate" => $this->getUser()->getId(), "tournamentStep" => $tournament->getStep(), "status" => "STARTED"]);
            if($endTrial){
                return $this->render('back/tournament/handle.html.twig', [
                    'tournament' => $tournament,
                    'trials' => $em->getRepository(Trial::class)->findBy(['tournament' => $tournament], ['tournamentStep' => 'ASC']),
                    'endTrial' => $endTrial,
                    'victoryTypes' => Trial::ENUM_VICTORY,
                ]);
            }
        }
        return $this->render('back/tournament/show.html.twig', [
            'tournament' => $tournament,
            'trials' => $em->getRepository(Trial::class)->findBy(['tournament' => $tournament], ['tournamentStep' => 'ASC'])
        ]);
    }

    #[Route('/{id}/start', name: 'tournament_start', methods: ['POST'])]
    #[IsGranted(TournamentVoter::START, 'tournament')]
    public function start(Request $request, Tournament $tournament, EntityManagerInterface $em): Response
    {
        if(!$this->isCsrfTokenValid('start'.$tournament->getId(), $request->request->get('_token'))){
            $this->addFlash('red', "SecurityError");
            return $this->redirectToRoute('back_trial_index', [], Response::HTTP_SEE_OTHER);
        }
        $tournament->setStatus("STARTED");
        $tournament->setStep(1); 
        $em->flush();

        return $this->redirectToRoute('back_tournament_show', [ 'id' => $tournament->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{tournament}/start/{trial}', name: 'tournament_trial_start', methods: ['POST'])]
    #[IsGranted(TrialVoter::EDIT, "trial")]
    public function startTrial(Request $request, Tournament $tournament,Trial $trial, EntityManagerInterface $entityManager): Response
    {
        if(!$this->isCsrfTokenValid('startTrial'.$trial->getId(), $request->request->get('_token'))){
            $this->addFlash('red', "SecurityError");
            return $this->redirectToRoute('back_trial_index', [], Response::HTTP_SEE_OTHER);
        }
        $trial->setBetStatus(0);
        $trial->setStatus("STARTED");
        $entityManager->flush();
        $this->addFlash('green', 'Trial started, don\'t forget to end it to continue tournament');
        return $this->redirectToRoute('back_tournament_show', [ 'id' => $tournament->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{tournament}/end/{trial}', name: 'tournament_trial_end', methods: ['POST'])]
    #[IsGranted(TrialVoter::EDIT, "trial")]
    public function endTrial(Request $request, Tournament $tournament,Trial $trial, EntityManagerInterface $entityManager, TrialRepository $trialRepository, TrialService $ts): Response
    {
        if(!$this->isCsrfTokenValid('endTrial'.$trial->getId(), $request->request->get('_token'))){
            $this->addFlash('red', "SecurityError");
            return $this->redirectToRoute('front_trial_show', ["id" => $trial->getId()], Response::HTTP_SEE_OTHER);
        }
        if(!$request->request->get('victoryType') || !$request->request->get('fighter')){
            $this->addFlash('red', "Missing parameters, send confirm form");
            return $this->redirectToRoute('front_trial_show', ["id" => $trial->getId()], Response::HTTP_SEE_OTHER);
        }
        $trials = $trialRepository->findOneBy(["tournament" => $tournament->getId(), "tournamentStep" => $tournament->getStep(), "status" => "STARTED"]);
        dd($trialRepository->findStepOpenedTrialsForTournament($tournament));
        $ts->endTrial($trial, $entityManager->getRepository(User::class)->findOneBy(['id' => $request->request->get('fighter')]),$request->request->get('victoryType') );
        $this->addFlash('green', "Ranking modified and trial ended");
        return $this->redirectToRoute('back_tournament_show', [ 'id' => $tournament->getId()], Response::HTTP_SEE_OTHER);
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
            return $this->redirectToRoute('back_tournament_index', ['status' => "CREATED"], Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('lock'.$tournament->getId(), $request->request->get('_token'))) {
            $ts->createTrialsForTournament($tournament);
            $tournament->setStatus("AWAITING");
            $em->flush();
            $this->addFlash('green', "Tournament initialized");
            return $this->redirectToRoute('back_tournament_index', ['status' => "AWAITING"], Response::HTTP_SEE_OTHER);
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
