<?php declare(strict_types=1);

namespace App\Event;

use App\Entity\Server;
use Symfony\Contracts\EventDispatcher\Event;

class GenerateFakePlayersEvent extends Event
{
    const NAME = 'generate_fake_players';

    private $server;

    /**
     * GenerateFakePlayersEvent constructor.
     *
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }
}