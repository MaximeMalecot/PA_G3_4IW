<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Invoice;
use App\Security\Voter\InvoiceVoter;
use App\Security\Voter\UserVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/invoice')]
class InvoiceController extends AbstractController
{
    #[Route('/user/{id}', name: 'invoice_user', methods: ['GET'])]
    #[IsGranted(UserVoter::SHOW_INVOICE, 'user')]
    public function index(User $user, InvoiceRepository $invoiceRepository): Response
    {
        return $this->render('front/invoice/index.html.twig', [
            'user' => $user,
            'invoices' => $invoiceRepository->findBy(['buyer' => $user], ['createdAt' => 'DESC'])
        ]);
    }

    #[Route('/show/{id}', name: 'invoice_show')]
    #[IsGranted(InvoiceVoter::SHOW, 'invoice')]
    public function show(Invoice $invoice): void
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        $html = $this->renderView('front/invoice/show.html.twig', [
            'invoice' => $invoice,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("invoice.pdf", [
            "Attachment" => true
        ]);
    }
}
