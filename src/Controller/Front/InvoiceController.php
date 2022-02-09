<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\Voter\InvoiceVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Entity\User;
use App\Entity\Invoice;

#[Route('/invoice')]
class InvoiceController extends AbstractController
{
    #[Route('/user/{id}', name: 'front_invoice', methods: ['GET'])]
    #[IsGranted(InvoiceVoter::SHOW, 'user')]
    public function index(User $user): Response
    {
        $invoices = $user->getInvoices();
        return $this->render('front/invoice/index.html.twig', [
            'controller_name' => 'TrialController',
            'invoices' => $invoices,
        ]);
    }

    #[Route('/show/{id}', name: 'front_show_invoice')]
    #[IsGranted(InvoiceVoter::SHOW, 'user')]
    public function show(Invoice $invoice): Response
    {
        return "hihihi";
        //render pdf of invoice
        /*return $this->render('front/invoice/show.html.twig', [
            'controller_name' => 'TrialController',
            'invoice' => $invoice,
        ]);*/
    }


}
