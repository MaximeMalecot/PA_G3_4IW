<?php

namespace App\Controller\Back;

use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ticket')]
class TicketController extends AbstractController
{
    #[Route('/', name: 'ticket_index', methods: ['GET', 'POST'])]
    public function index(Request $request, TicketRepository $ticketRepository): Response
    {
        $roleWanted = $request->request->get('roleWanted') ?? "Fighter";
        $status = $request->request->get('status') ?? "CREATED";
        return $this->render('back/ticket/index.html.twig', [
            'tickets' => $ticketRepository->findBy([ "roleWanted" => $roleWanted, "status" => $status]),
            'status' => $status,
            'roleWanted' => $roleWanted,
        ]);
    }

    #[Route('/{id}', name: 'ticket_show', methods: ['GET'])]
    public function show(Ticket $ticket): Response
    {
        return $this->render('back/ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{id}', name: 'ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_ticket_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/accept/{id}', name: 'ticket_accept', methods: ['POST'])]
    public function accept(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('accept'.$ticket->getId(), $request->request->get('_token'))) {
            $ticket->setStatus("ACCEPTED");
            $entityManager->flush();
            //SHOULD SEND EMAIL
        }

        return $this->redirectToRoute('back_ticket_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/refuse/{id}', name: 'ticket_refuse', methods: ['POST'])]
    public function refuse(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('refuse'.$ticket->getId(), $request->request->get('_token'))) {
            $ticket->setStatus("REFUSED");
            $entityManager->flush();
            //SHOULD SEND EMAIL
        }

        return $this->redirectToRoute('back_ticket_index', [], Response::HTTP_SEE_OTHER);
    }
}
