<?php

namespace App\Controller;

use App\Entity\Player;
use App\Event\CreateNewPlayerPlayerStatsEvent;
use App\Event\SetPlayerAttributesForNewPlayerEvent;
use App\Form\PlayerFormType;
use App\Repository\PlayerRepository;
use App\Security\Voter\PlayerVoter;
use App\Service\AttributeService;
use App\Service\PlayerService;
use App\Service\PlayerStatsService;
use App\Service\SeasonService;
use App\Service\ServerService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class PlayerController
 * @package App\Controller
 *
 * @Route("/player")
 */
class PlayerController extends BaseController
{
    const DEFAULT_MONEY = 100000;

    /**
     * @Route("/new", name="new_player", methods={"GET", "POST"})
     *
     * @param Request                  $request
     * @param PlayerStatsService       $playerStatsService
     * @param EventDispatcherInterface $eventDispatcher
     * @param PlayerService            $playerService
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function new(
        Request $request,
        PlayerStatsService $playerStatsService,
        EventDispatcherInterface $eventDispatcher,
        PlayerService $playerService
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if($this->getUser()->getPlayer() instanceof Player) {
            $this->addFlash('warning', 'player.has_player');

            return $this->redirectToRoute('home');
        }

        $player = new Player();
        $player->setIsRealPlayer(true);
        $player->setUser($this->getUser());
        $player->setMoney(self::DEFAULT_MONEY);

        $form = $this->createForm(PlayerFormType::class, $player);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($player);
            $em->flush();

            $event = new CreateNewPlayerPlayerStatsEvent($player);
            $eventDispatcher->dispatch($event, CreateNewPlayerPlayerStatsEvent::NAME);

            $event = new SetPlayerAttributesForNewPlayerEvent($player);
            $eventDispatcher->dispatch($event, SetPlayerAttributesForNewPlayerEvent::NAME);

            list($teamName, $draftPick) = $playerService->draftPlayer($player);

            if($draftPick !== 0) {
                $this->addFlash('draftPick', $draftPick);
                $this->addFlash('teamName', $teamName);
                $this->addFlash('success', 'player.created');
            } else {
                $this->addFlash('warning', 'player.no_teams');
            }

            return $this->redirectToRoute('player_index', [
                'id' => $player->getId()
            ]);
        }

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'player/new.html.twig',
            'player/lightmode/new.html.twig'
        ), [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }

    /**
     * @Route("/free-agents", name="player_free_agents", methods={"GET"})
     *
     * @param Request $request
     * @param PlayerRepository $playerRepository
     * @param PaginatorInterface $paginator
     *
     * @return Response
     */
    public function freeAgents(
        Request $request,
        PlayerRepository $playerRepository,
        PaginatorInterface $paginator
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if(!$this->getUser()->getPlayer()) {
            $this->addFlash(
                'warning',
                'player.no_player'
            );

            return $this->redirectToRoute('team');
        }

        $queryBuilder = $playerRepository->getFreeAgents();

        $players = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'player/free.agent.html.twig',
            'player/lightmode/free.agent.html.twig'
        ), [
            'players' => $players
        ]);
    }

    /**
     * @Route("/{id}", name="player_index", methods={"GET"})
     *
     * @param int $id
     * @param PlayerRepository $playerRepository
     * @param SeasonService $seasonService
     * @param PlayerStatsService $playerStatsService
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function index(
        int $id,
        PlayerRepository $playerRepository,
        SeasonService $seasonService,
        PlayerStatsService $playerStatsService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if(!$this->getUser()->getPlayer()) {
            $this->addFlash(
                'warning',
                'player.no_player'
            );

            return $this->redirectToRoute('team');
        }

        $this->denyAccessUnlessGranted(PlayerVoter::HAS_PLAYER);

        $player = $playerRepository->find($id);

        if(!$player) {
            $this->addFlash('warning', 'player.not_found');

            return $this->redirectToRoute('home');
        }

        $playerCareerPlayerStats = $playerStatsService->getCareerPlayerStats($player);

        return $this->render('player/index.html.twig', [
            'player' => $player,
            'season' => $seasonService->getActiveSeason($player->getServer())->getId(),
            'careerPlayerStats' => $playerCareerPlayerStats
        ]);
    }
}
