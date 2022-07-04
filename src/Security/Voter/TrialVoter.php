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
    const CREATE = 'create';
    const CONSULT = 'consult';
    const TRIAL_ANSWER = "trial_answer";
    const CHALLENGE_ANSWER = "challenge_answer";

    protected function supports(string $attribute, $subject): bool
    {
        if(in_array($attribute, [self::EDIT, self::DELETE, self::CREATE, self::CONSULT, self::TRIAL_ANSWER, self::CHALLENGE_ANSWER])){
            if(in_array($attribute, [self::CREATE, self::CONSULT])){
                return true;
            } else {
                return $subject instanceof Trial;
            }
        }
        return false;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return in_array('ROLE_ADJUDICATE', $user->getRoles());
                break;
            case self::EDIT:
                return $this->canEdit($subject, $user);
                break;
            case self::DELETE:
                return in_array('ROLE_ADMIN', $user->getRoles()) || $this->canEdit($subject, $user);
                break;
            case self::CONSULT:
                return in_array('ROLE_FIGHTER', $user->getRoles());
                break;
            case self::TRIAL_ANSWER:
                return in_array('ROLE_FIGHTER', $user->getRoles()) && $subject->getFighters()->contains($user);
                break;
            case self::CHALLENGE_ANSWER:
                return $this->canAnswerChallenger($subject, $user);
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
        }else if( $trial->getStatus() === 'REFUSED'){
            return false;
        } else {
            if($user == $trial->getAdjudicate()){
                return true;
            }
            return false;
        }
    }

    protected function canAnswerChallenger(Trial $trial, User $user): bool
    {
        if($trial->getStatus() === "CREATED"){
            return in_array('ROLE_FIGHTER', $user->getRoles()) && $trial->getFighters()->contains($user) && $trial->getUpdatedBy() !== $user;
        }
        if($trial->getStatus() === "ACCEPTED"){
            return in_array("ROLE_ADJUDICATE", $user->getRoles());
        }
    }
    
}