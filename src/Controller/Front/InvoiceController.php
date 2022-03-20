<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Invoice;
use App\Security\Voter\InvoiceVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/invoice')]
class InvoiceController extends AbstractController
{
    #[Route('/user/{id}', name: 'front_invoice', methods: ['GET'])]
    #[IsGranted(InvoiceVoter::SHOW, 'user')]
    public function index(User $user): Response
    {
        return $this->render('front/invoice/index.html.twig', [
            'controller_name' => 'TrialController',
            'invoices' => $user->getInvoices(),
        ]);
    }

    #[Route('/show/{id}', name: 'front_invoice_show')]
    #[IsGranted(InvoiceVoter::SHOW, 'user')]
    public function show(Invoice $invoice): Response
    {
        dd('render pdf of invoice');
        return $this->render('front/invoice/show.html.twig', [
            'controller_name' => 'TrialController',
            'invoice' => $invoice,
        ]);
    }


}
