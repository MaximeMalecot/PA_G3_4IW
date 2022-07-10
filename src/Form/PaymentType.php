<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints as Assert;
class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
                ->add('credits',NumberType::class, [
                'constraints' => array( new Assert\Range(array('min' => 5, 'minMessage' => "Vous devez acheter au minimum 5 crédits !")) ),
                'label' => "Crédits",
                "attr" => ["class" => "form-control"]
        ]);
        ;
    }

  
}
