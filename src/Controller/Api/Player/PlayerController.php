<?php declare(strict_types=1);

namespace App\Controller\Api\Player;

use App\Controller\BaseController;
use App\Entity\Player;
use App\Security\Voter\PlayerVoter;
use App\Service\PlayerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/player")
 */
class PlayerController extends BaseController
{
    /**
     * @Route("/player-details/{playerId}", methods={"GET"}, name="api_player_details")
     *
     * @param PlayerService $playerService
     * @param Request $request
     * @param int $playerId
     *
     * @return JsonResponse
     */
    public function playerDetailsAction(PlayerService $playerService, Request $request, int $playerId): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted(PlayerVoter::HAS_PLAYER);

        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Page not found'], 404);
        }

        $player = $this->getDoctrine()->getManager()->getRepository(Player::class)->find($playerId);

        if(!$player instanceof Player) {
            return new JsonResponse(['error' => 'Page not found'], 404);
        }

        $data = $playerService->getPlayerDetails($player);

        return new JsonResponse(['details' => $data]);
    }
}