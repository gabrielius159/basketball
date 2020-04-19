<?php declare(strict_types=1);

namespace App\Controller\Api\Player;

use App\Controller\BaseController;
use App\Entity\Player;
use App\Security\Voter\PlayerVoter;
use App\Service\PlayerAttributeService;
use App\Utils\PlayerAttribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PlayerAttributeController
 *
 * @package App\Controller\Api\Player
 *
 * @Route("/api/player-attribute")
 */
class PlayerAttributeController extends BaseController
{
    const ERROR_NOT_ENOUGH_MONEY = 505;
    const ERROR_SKILL_NOT_FOUND = 506;
    const ERROR_MAX_LEVEL = 507;

    /**
     * @Route("/{playerId}", name="api_player_attribute", methods={"GET"})
     *
     * @param Request                $request
     * @param PlayerAttributeService $playerAttributeService
     * @param int                    $playerId
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function playerAttributesAction(
        Request $request,
        PlayerAttributeService $playerAttributeService,
        int $playerId
    ): JsonResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted(PlayerVoter::HAS_PLAYER);

        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Page not found'], 404);
        }

        $playerAttributes = $playerAttributeService->getPlayerPlayerAttributes(
            $playerId,
            $this->getUser()->getPlayer()->getId() === $playerId
        );

        if(!$playerAttributes) {
            return new JsonResponse(['items' => [], 'playerId' => $playerId]);
        }

        return new JsonResponse([
            'items' => $playerAttributes,
            'playerId' => $playerId,
            'lightMode' => $this->isUserInLightMode($request)
        ]);
    }

    /**
     * @Route("/improve/{attributeId}", name="api_player_attribute_improve", methods={"POST"})
     *
     * @param Request                $request
     * @param int                    $attributeId
     * @param PlayerAttributeService $playerAttributeService
     * @param PlayerAttribute        $playerAttributeUtil
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function playerAttributeImproveAction(
        Request $request,
        int $attributeId,
        PlayerAttributeService $playerAttributeService,
        PlayerAttribute $playerAttributeUtil
    ): JsonResponse {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted(PlayerVoter::HAS_PLAYER);

        if(!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Page not found'], 404);
        }

        /**
         * @var Player $player
         */
        $player = $this->getUser()->getPlayer();

        $playerAttribute = $playerAttributeService->getPlayerPlayerAttribute(
            $player,
            $attributeId
        );

        if(!$playerAttribute) {
            return new JsonResponse(['error' => self::ERROR_SKILL_NOT_FOUND], 404);
        }

        $price = $playerAttributeUtil->getPlayerAttributeImprovePrice(
            $player,
            $playerAttribute->getAttribute()->getGameType()->getName(),
            $playerAttribute->getValue()
        );

        if($price > $player->getMoney()) {
            return new JsonResponse(['error' => self::ERROR_NOT_ENOUGH_MONEY]);
        }

        if($playerAttribute->getValue() >= 99) {
            $playerAttributeService->fixPlayerPlayerAttributeValue($playerAttribute);

            return new JsonResponse(['error' => self::ERROR_MAX_LEVEL]);
        }

        $playerAttributeService->improvePlayerPlayerAttribute($playerAttribute, $price);

        return new JsonResponse(['success' => true]);
    }
}