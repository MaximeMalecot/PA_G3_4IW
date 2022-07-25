<?php

namespace App\Form;

use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TicketFighterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roleWanted', ChoiceType::class, [
                'choices'  => [
                    'Adjudicate' => 'Adjudicate'
                ],
                'label' => 'Statut',
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('description', null, [
                'label' => 'Décrivez pourquoi vous pensez pouvoir avoir ce rôle',
                'attr' => ['class' => 'form-control mb-3']
            
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
