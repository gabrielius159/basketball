<?php

namespace App\Security\Voter;

use App\Entity\Player;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class PlayerTeamVoter
 *
 * @package App\Security\Voter
 */
class PlayerTeamVoter extends Voter
{
    const HAS_TEAM = 'HAS_TEAM';
    const HAS_TEAM_FOR_SIGNING_NEW_CONTRACT = 'HAS_TEAM_FOR_SIGNING_NEW_CONTRACT';

    /**
     * @param string $attribute
     * @param mixed $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::HAS_TEAM, self::HAS_TEAM_FOR_SIGNING_NEW_CONTRACT]);
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }
        
        switch ($attribute) {
            case self::HAS_TEAM: {
                if($user->getPlayer()->getTeam()) {
                    return true;
                }

                break;
            }
            case self::HAS_TEAM_FOR_SIGNING_NEW_CONTRACT: {
                if(!$user->getPlayer()->getTeam()) {
                    return true;
                }

                break;
            }
        }

        return false;
    }
}
