<?php

namespace App\Controller\Front;

use App\Entity\Invoice;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\PaymentType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class PaymentController extends AbstractController
{
    #[Route('/payment/credit', name: 'credit', methods: ['GET','POST'])]
    public function credit(Request $request): Response
    {
        $userConnected = $this->getUser();
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect("/login");
        }

        if (in_array('ROLE_ADJUDICATE', $userConnected->getRoles())){
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour accéder à cette page');
            return $this->redirect("/login");
        }


        $form = $this->createForm(PaymentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('payment', ["credit" => $form->get('credits')->getData() ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Front/payment/index.html.twig', [
            'form' => $form,
        ]);

    }

    
    #[Route('/payment/success', name: 'success')]
    public function success(): Response
    {
        $userConnected = $this->getUser();
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect("/login");
        }

        if (in_array('ROLE_ADJUDICATE', $userConnected->getRoles())){
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour accéder à cette page');
            return $this->redirect("/login");
        }

        $session = new Session();

        if ( $session->get('payment_intent') == null ){
            return $this->redirect("/login");
        }

        $stripe = new \Stripe\StripeClient(
            $_ENV['STRIPE_KEY']
        );
        
        $payment_intent = $stripe->paymentIntents->retrieve(
            $session->get('payment_intent'),
            []
        );

       if ($payment_intent->status == "succeeded"){

            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->find($this->getUser()->getId());
            $credits = $user->getCredits();
            $credits += $session->get('credit');
            $user->setCredits($credits);

            $entityManager->flush();
            $invoice = new Invoice();
            $invoice->setBuyer($user);
            $invoice->setCreditAmount($session->get('credit'));
            $invoice->setPrice($session->get('credit') / 10);
            $entityManager->persist($invoice);
            $entityManager->flush();
            $session->remove('credit');
            $session->remove('payment_intent');
          
            return $this->render('front/payment/success.html.twig');
       }else{
        return $this->render('front/payment/cancel.html.twig');
       }

    }

    #[Route('/payment/cancel', name: 'cancel')]
    public function cancel(): Response
    {

        $userConnected = $this->getUser();
        
        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect("/login");
        }

        if (in_array('ROLE_ADJUDICATE', $userConnected->getRoles())){
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour accéder à cette page');
            return $this->redirect("/login");
        }
        return $this->render('front/payment/cancel.html.twig');
    }

    #[Route('/payment/{credit}', name: 'payment')]
    public function payment(int $credit): Response
    {
        if ($credit < 5){
            $this->addFlash('danger', 'Vous devez acheter au moins 5 crédits');
            return $this->redirect("/payment/credit");
        }

        $userConnected = $this->getUser();

        if (!$this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirect("/login");
        }

        if (in_array('ROLE_ADJUDICATE', $userConnected->getRoles())){
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour accéder à cette page');
            return $this->redirect("/login");
        }

        $session = new Session();
    
        $price = $credit / 10;
       
        \Stripe\Stripe::setApiKey($_ENV['STRIPE_KEY']);
        $YOUR_DOMAIN = 'http://localhost:81/';
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => 100 * $price,
                    'product_data' => [
                        'name' => 'Achat de crédits',
                        'images' => ["https://conseils.casalsport.com/wp-content/uploads/2019/05/entretenir-ses-gants-de-boxe.jpg"],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . 'payment/success',
            'cancel_url' => $YOUR_DOMAIN . 'payment/cancel',
        ]);
        
        $session->set('credit', $credit);
        $session->set('payment_intent', $checkout_session["payment_intent"]);

        return $this->redirect($checkout_session->url);
    }

}
