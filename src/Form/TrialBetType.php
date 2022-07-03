<?php

namespace App\Form;

use App\Entity\Bet;
use App\Entity\Tournament;
use App\Entity\Trial;
use App\Entity\User;
use App\Repository\TrialRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class TrialBetType extends AbstractType
{
    public function __construct(private Security $security, private TrialRepository $trialRepository)
    {
    }

    // https://symfony.com/doc/current/form/dynamic_form_modification.html
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('trial', HiddenType::class, [
                'data' => $this->trialRepository->find($options['trial_id']),
                'data_class' => Trial::class,
            ])
            ->add('better', HiddenType::class, [
                'data' => $this->security->getUser(),
                'data_class' => User::class,
            ])
            ->add('bettee', EntityType::class, [
                'class'=> User::class,
                'choice_label'=>'nickname',
                'choice_value'=>'id',
                'multiple' => false,
                'expanded' => false,
                'label' => 'Fighter',
                'required' => true,
                'choices' => $this->trialRepository->find($options['trial_id'])->getFighters(),
            ])
            ->add('amount', IntegerType::class, [
                'label' => 'Montant',
                'attr' => [
                    'placeholder' => 'Montant du pari',
                    'min' => 1,
                    'max' => $this->security->getUser()->getCredits(),
                    'step' => 1,
                ],
            ])
            ->add('save', SubmitType::class, ['label' => "Parier"]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bet::class,
            'trial_id' => null,
        ]);
        $resolver->setAllowedTypes('trial_id', ['int', 'string']);
    }
}
