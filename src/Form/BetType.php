<?php

namespace App\Form;

use App\Entity\Bet;
use App\Entity\Trial;
use App\Repository\TrialRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class BetType extends AbstractType
{
    public function __construct(private Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('trial', EntityType::class, [
                'label' => 'Trial',
                'class' => Trial::class,
                'query_builder' => function (TrialRepository $tr) {
                    $qb = $tr->createQueryBuilder('t');
                    return $qb
                        ->innerJoin('t.fighters', 'f')
                        ->where($qb->expr()->in('t.status',array("AWAITING")))
                        ->andWhere($qb->expr()->isNotNull('t.adjudicate'));
                },
                 'multiple' => false,
                 'expanded' => false,
                'placeholder' => 'Choisissez un trial'])
            ->add('amount', IntegerType::class, [
                'label' => 'Montant',
                'attr' => [
                    'placeholder' => 'Montant du pari',
                    'min' => 1,
                    'max' => $this->security->getUser()->getCredits(),
                    'step' => 1,
                ],
            ])
            ->add('save', SubmitType::class, ['label' => "Parier"])
//            ->add('tournament' )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bet::class,
        ]);
    }
}
