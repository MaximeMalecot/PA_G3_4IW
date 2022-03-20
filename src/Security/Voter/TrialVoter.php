<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Trial;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TrialVoter extends Voter
{
    /*
        TO IMPLEMENT THE VOTER IN A CONTROLLER JUST DO :
        #[IsGranted(TrialVoter::EDIT, 'trial')]
    */
    const EDIT = 'edit';
    const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Trial;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
                break;
            case self::DELETE:
                return in_array('ROLE_ADMIN', $user->getRoles()) || $this->canEdit($subject, $user);
                break;
        }

        return false;
    }

    /**
     * @param Trial $trial
     * @param User $user
     * @return bool
     */
    protected function canEdit(Trial $trial, User $user): bool
    {
        if( in_array($trial->getStatus(), ['CREATED', 'DATE_ACCEPTED'])){
            if( $trial->getFighters()->contains($user) || $user == $trial->getAdjudicate())
            {
                return true;
            }
            return false;
        }else if( $trial->getStatus() == 'REFUSER'){
            return false;
        } else {
            if($user == $trial->getAdjudicate()){
                return true;
            }
            return false;
        }
    }
}