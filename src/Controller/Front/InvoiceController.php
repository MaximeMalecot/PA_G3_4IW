<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Entity\Invoice;
use App\Security\Voter\InvoiceVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\InvoiceRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

#[Route('/invoice')]
class InvoiceController extends AbstractController
{
    #[Route('/user/{id}', name: 'invoice_user', methods: ['GET'])]
    // #[IsGranted(InvoiceVoter::SHOW, 'user')]
    public function index(User $user): Response
    {
        return $this->render('front/invoice/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/show/{id}', name: 'invoice_show')]
    // #[IsGranted(InvoiceVoter::SHOW, 'user')]
    public function show(Invoice $invoice, InvoiceRepository $invoiceRepository, int $id): Response
    {

        $invoice = $invoiceRepository->find($id);
        
        if ($invoice){

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
        }else{
            return $this->redirectToRoute('front_invoice_user', [], Response::HTTP_SEE_OTHER);
        }
       
       
    }


}
