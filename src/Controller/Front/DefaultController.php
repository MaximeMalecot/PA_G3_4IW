<?php

namespace App\Controller\Front;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Stripe\Checkout;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default')]
    public function index(): Response
    {
        return $this->render('front/default/index.html.twig', [
            'controller_name' => 'FRONT',
        ]);
        
    }
    #[Route('/payment', name: 'payment')]
    public function payment(): Response
    {
        $securityContext = $this->container->get('security.authorization_checker');

        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') === false) {
        
            return $this->redirect("/login");
        }

        \Stripe\Stripe::setApiKey('sk_test_51LGVfsBYnbPwVzITdZ1beyU8wGKOFIZDYQNHbysLI7wof5e2n3SPGhdkPVsOvkzsfFWnb8btlVhoCuG5X3Kk1OqA004NNlVIXq');
        $YOUR_DOMAIN = 'http://localhost:81/';
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => 100 * 100,
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
        return $this->redirect($checkout_session->url);
    }

    #[Route('/success', name: 'success')]
    public function success() : Response{

        return $this->render('front/payment/success.html.twig');
    }

    #[Route('/cancel', name: 'cancel')]
    public function cancel() : Response{

        return $this->render('front/payment/cancel.html.twig');
    }
}