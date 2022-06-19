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
    const TRIAL_ANSWER = "trial_answer";
    const CHALLENGE_ANSWER = "challenge_answer";

    protected function supports(string $attribute, $subject): bool
    {
        if(in_array($attribute, [self::EDIT, self::DELETE, self::CREATE, self::TRIAL_ANSWER, self::CHALLENGE_ANSWER])){
            if($attribute == self::CREATE){
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
            case self::TRIAL_ANSWER :
                return canAnswerTrial($user, $subject);
                break;
            case self::CHALLENGE_ANSWER :
                return canAnswerChallenge($user, $subject);
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

    protected function canAnswerTrial(User $user, Trial $trial): bool 
    {
        if( in_array('ROLE_FIGHTER', $user->getRoles())){
            if($trial->getStatus() === "CREATED"){
                return true;
            } 
            if ($trial->getStatus() === "DATE_ACCEPTED" && $trial->getUpdatedBy()->getId() !== $user->getId()){
                return true;
            }
        }
        return false;
    }

    protected function canAnswerChallenge(User $user, Trial $trial): bool 
    {
        if( in_array('ROLE_FIGHTER', $user->getRoles())){
            if($trial->getStatus() === "CREATED"  && $trial->getCreatedBy()->getId() !== $user->getId()){
                return true;
            } 
        }
        return false;
    }
}