<?php

namespace App\Form;

use App\Entity\Bet;
use App\Entity\Trial;
use App\Entity\User;
use App\Repository\TournamentRepository;
use App\Repository\TrialRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class BetType extends AbstractType
{
    public function __construct(private Security $security,
                                private TrialRepository $trialRepository,
                                private TournamentRepository $tournamentRepository)
    {
    }

    // https://symfony.com/doc/current/form/dynamic_form_modification.html
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('bettee', EntityType::class, [
                'class'=> User::class,
                'choice_label'=>'nickname',
                'choice_value'=>'id',
                'multiple' => false,
                'expanded' => false,
                'label' => 'Combattant',
                'invalid_message' => 'Le combattant est invalide ou n\'existe pas.',
                'required' => true,
                'choices' => match ($options['bet_type']) {
                    'trial' => $options['entity']->getFighters(),
                    'tournament' => $options['entity']->getParticipantFromRole("ROLE_FIGHTER"),
                },
                'attr' => ['class' => 'form-control mb-3']
            ]);
        
        if($options['bet_type'] === 'trial'){
            $builder->add('victoryType', ChoiceType::class, [
                'choices' => [
                    Trial::ENUM_VICTORY[0] => Trial::ENUM_VICTORY[0],
                    Trial::ENUM_VICTORY[1] => Trial::ENUM_VICTORY[1],
                    Trial::ENUM_VICTORY[2] => Trial::ENUM_VICTORY[2],
                ],
                'label' => 'Type de victoire',
                'required' => true,
                'attr' => ['class' => 'form-control mb-3']
            ]);
        }

        $builder
        ->add('amount', IntegerType::class, [
            'label' => 'Montant',
            'required' => true,
            'invalid_message' => 'Le montant est invalide.',
            'attr' => [
                'placeholder' => 'Montant du pari',
                'min' => 1,
                'max' => $this->security->getUser()->getCredits(),
                'step' => 1,
            ],
            'attr' => ['class' => 'form-control mb-3']
        ])
        ->add('save', SubmitType::class, ['label' => "Parier",   'attr' => ['class' => 'btn btn-bettle']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bet::class,
            'bet_type' => '',
            'entity' => null,
        ]);
        $resolver->setAllowedTypes('entity', ['App\Entity\Trial', 'App\Entity\Tournament']);
        $resolver->setAllowedTypes('bet_type', ['string']);
        $resolver->setAllowedValues('bet_type', ['trial', 'tournament']);
    }
}
