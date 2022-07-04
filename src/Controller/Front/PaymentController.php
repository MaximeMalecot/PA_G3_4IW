<?php

namespace App\Controller\Front;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Checkout;
use App\Repository\UserRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class PaymentController extends AbstractController
{
    #[Route('/credit', name: 'credit')]
    public function credit(): Response
    {

        return $this->render('front/payment/index.html.twig');
    }

    #[Route('/payment/{credit}', name: 'payment')]
    public function payment(int $credit): Response
    {
        // $securityContext = $this->container->get('security.authorization_checker');

        // if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') === false) {

        //     return $this->redirect("/login");
        // }

        $credits = [ 100 => "10", 250 => "25", 500 => "50", 750 => "65"];

        if (!in_array($credit, array_keys($credits))){
            return $this->redirect("/login");
        }

        $session = new Session();
    
        $price = $credits[$credit];
       
        \Stripe\Stripe::setApiKey('sk_test_51LGVfsBYnbPwVzITdZ1beyU8wGKOFIZDYQNHbysLI7wof5e2n3SPGhdkPVsOvkzsfFWnb8btlVhoCuG5X3Kk1OqA004NNlVIXq');
        $YOUR_DOMAIN = 'http://localhost:81/';
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => 100 * $price,
                    'product_data' => [
                        'name' => 'Achat de crédits',
                        'images' => ["https://i.imgur.com/EHyR2nP.png"],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . 'success',
            'cancel_url' => $YOUR_DOMAIN . 'cancel',
        ]);
        
        $session->set('credit', $credit);
        $session->set('payment_intent', $checkout_session["payment_intent"]);

        return $this->redirect($checkout_session->url);
    }

    #[Route('/success', name: 'success')]
    public function success(Request $request, UserRepository $userRepository): Response
    {

        $session = new Session();

        if ( $session->get('payment_intent') == null ){
            return $this->redirect("/login");
        }

        $stripe = new \Stripe\StripeClient(
            'sk_test_51LGVfsBYnbPwVzITdZ1beyU8wGKOFIZDYQNHbysLI7wof5e2n3SPGhdkPVsOvkzsfFWnb8btlVhoCuG5X3Kk1OqA004NNlVIXq'
        );
        
        $payment_intent = $stripe->paymentIntents->retrieve(
            $session->get('payment_intent'),
            []
        );

        //$userConnected = $this->get('security.token_storage')->getToken()->getUser();
       if ($payment_intent->status == "succeeded"){

            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->find(129);
            $credits = $user->getCredits();
            $credits += $session->get('credit');
            $user->setCredits($credits);

            $entityManager->flush();
            $session->remove('credit');
            $session->remove('payment_intent');
          
            return $this->render('front/payment/success.html.twig');
       }else{
        return $this->render('front/payment/cancel.html.twig');
       }

    }

    #[Route('/cancel', name: 'cancel')]
    public function cancel(): Response
    {

        return $this->render('front/payment/cancel.html.twig');
    }
}
