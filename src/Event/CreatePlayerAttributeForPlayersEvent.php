<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Attribute;
use Symfony\Contracts\EventDispatcher\Event;

class CreatePlayerAttributeForPlayersEvent extends Event
{
    const NAME = 'create_player_attribute_for_players';

    private $attribute;

    /**
     * CreatePlayerAttributeForPlayersEvent constructor.
     *
     * @param Attribute $attribute
     */
    public function __construct(Attribute $attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * @return Attribute
     */
    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }
}