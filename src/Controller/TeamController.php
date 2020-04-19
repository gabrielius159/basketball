<?php

namespace App\Controller;

use App\Entity\Badge;
use App\Entity\GameDay;
use App\Entity\Player;
use App\Entity\Team;
use App\Event\CreateBadgeForPlayerEvent;
use App\Form\BuyoutFormType;
use App\Form\PlayerSignContractFormType;
use App\Repository\GameDayRepository;
use App\Repository\SeasonRepository;
use App\Repository\TeamRepository;
use App\Repository\TrainingCampRepository;
use App\Security\Voter\PlayerTeamVoter;
use App\Security\Voter\PlayerVoter;
use App\Service\BadgeService;
use App\Service\PlayerService;
use App\Service\SeasonService;
use App\Service\ServerService;
use App\Service\TeamService;
use App\Utils\Award;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class TeamController
 *
 * @Route("/teams")
 *
 * @package App\Controller
 */
class TeamController extends BaseController
{
    /**
     * @Route("/", name="team", methods={"GET"}, name="team")
     *
     * @param TeamRepository $teamRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param TeamService $teamService
     * @param ServerService $serverService
     * @param SeasonService $seasonService
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    public function index(
        TeamRepository $teamRepository,
        PaginatorInterface $paginator,
        Request $request,
        TeamService $teamService,
        ServerService $serverService,
        SeasonService $seasonService
    ): Response {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $standings = $teamService->getStandings();
        $queryBuilder = $teamRepository->findAllWithQueryBuilder();

        $teams = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            12
        );

        $server = $serverService->getCurrentServer();
        $season = $seasonService->getActiveSeason($server);

        $leaders = $teamService->getLeagueLeaders($server);
        $todayGame = $seasonService->getTodayGame($server);
        $twoGames = $seasonService->getTwoUpcomingGames($server);
        $lastYearChampions = $seasonService->getLastSeasonChampions(
            $season
        );
        $mvp = $seasonService->getLastSeasonAwardWinnerByAwardType($season, Award::PLAYER_MVP);
        $dpoy = $seasonService->getLastSeasonAwardWinnerByAwardType($season, Award::PLAYER_DPOY);
        $roty = $seasonService->getLastSeasonAwardWinnerByAwardType($season, Award::PLAYER_ROTY);

        return $this->render($this->getTemplateByTemplateMode($request, 'team/index.html.twig', 'team/lightmode/index.html.twig'), [
            'teams' => $teams,
            'standings' => $standings,
            'leaders' => $leaders,
            'todayGame' => $todayGame,
            'twoGames' => $twoGames,
            'twoGamesCount' => count($twoGames),
            'champions' => $lastYearChampions,
            'mvp' => $mvp,
            'dpoy' => $dpoy,
            'roty' => $roty,
            'season' => $season
        ]);
    }

    /**
     * @Route("/buyout", name="team_buyout", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param TeamService $teamService
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function buyout(Request $request, TeamService $teamService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted(PlayerVoter::HAS_PLAYER);
        $this->denyAccessUnlessGranted(PlayerTeamVoter::HAS_TEAM);

        $form = $this->createForm(BuyoutFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $teamService->buyoutPlayerFromTeam($this->getUser()->getPlayer());

            $this->addFlash('warning', 'user.team_left');

            return $this->redirectToRoute('player_index', [
                'id' => $this->getUser()->getPlayer()->getId()
            ]);
        }

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'team/buyout.html.twig',
            'team/lightmode/buyout.html.twig'
        ), [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/sign/{id}", name="team_sign", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param TeamService $teamService
     * @param int $id
     * @param PlayerService $playerService
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function join(
        Request $request,
        TeamService $teamService,
        int $id,
        PlayerService $playerService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted(PlayerVoter::HAS_PLAYER);
        $this->denyAccessUnlessGranted(PlayerTeamVoter::HAS_TEAM_FOR_SIGNING_NEW_CONTRACT);

        $team = $teamService->findOneById($id);

        if(!$team) {
            $this->addFlash('warning', 'admin.team.not_found');

            return $this->redirectToRoute('team');
        }

        if(count($team->getRealPlayers()) > 9) {
            $this->addFlash('warning', 'user.not_interested');

            return $this->redirectToRoute('team');
        }

        $teamCount = $teamService->getTeamsCount($this->getUser()->getPlayer()->getServer());
        $gamesPlayed = $team->getCurrentTeamStatus()->getWin() + $team->getCurrentTeamStatus()->getLose();

        if($gamesPlayed >= $teamCount) {
            $this->addFlash('warning', 'user.cannot_join');

            return $this->redirectToRoute('team');
        }

        $salary = $playerService->generateSalaryWithTeamBudget($this->getUser()->getPlayer(), $team);

        $form = $this->createForm(PlayerSignContractFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $teamService->signPlayerToTeam($this->getUser()->getPlayer(), $team, $salary, $form->getData()['years']);

            $this->addFlash('success', 'Congratulations! You signed contract with ' . $team->getCity() . ' ' . $team->getName() . '.');

            return $this->redirectToRoute('team');
        }

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'team/sign.html.twig',
            'team/lightmode/sign.html.twig'
        ), [
            'form' => $form->createView(),
            'salary' => $salary,
            'teamCount' => $teamCount,
            'gamesPlayed' => $gamesPlayed
        ]);
    }

    /**
     * @Route("/schedule/{id}", name="team_schedule", methods={"GET"}, defaults={"id" = 0}, options={"expose"=true})
     *
     * @param GameDayRepository $gameDayRepository
     * @param ServerService $serverService
     * @param SeasonService $seasonService
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param SeasonRepository $seasonRepository
     * @param int $id
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function schedule(
        GameDayRepository $gameDayRepository,
        ServerService $serverService,
        SeasonService $seasonService,
        PaginatorInterface $paginator,
        Request $request,
        SeasonRepository $seasonRepository,
        int $id = 0
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $season = $id !== 0 ? $seasonRepository->find($id) : null;
        $season = $season ? $season : $seasonService->getActiveSeason($serverService->getCurrentServer());

        $queryBuilder = $gameDayRepository->getSchedule(
            $season
        );

        $schedule = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'team/schedule.html.twig',
            'team/lightmode/schedule.html.twig'
        ), [
            'schedule' => $schedule,
            'seasonIds' => $seasonRepository->findAllSeasonIds($serverService->getCurrentServer()),
            'selectedSeason' => $season ? $season->getId() : 1
        ]);
    }

    /**
     * @Route("/view/{id}", name="team_view", methods={"GET"})
     *
     * @param int $id
     * @param TeamRepository $teamRepository
     * @param Request $request
     *
     * @return Response
     */
    public function view(int $id, TeamRepository $teamRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $team = $teamRepository->find($id);

        if(!$team) {
            $this->addFlash('warning', 'admin.team.not_found');

            return $this->redirectToRoute('team');
        }

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'team/view.html.twig',
            'team/lightmode/view.html.twig'
        ), [
            'team' => $team,
            'players' => count($team->getPlayers()),
            'searchingFor' => Team::DEFAULT_PLAYER_LIMIT_IN_TEAM - count($team->getRealPlayers())
        ]);
    }

    /**
     * @Route("/training-camps", name="team_training_camp", methods={"GET"})
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param TrainingCampRepository $trainingCampRepository
     *
     * @return Response
     */
    public function trainingCamps(
        Request $request,
        PaginatorInterface $paginator,
        TrainingCampRepository $trainingCampRepository
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if(!$this->getUser()->getPlayer()) {
            $this->addFlash(
                'warning',
                'user.training_camp.no_player'
            );

            return $this->redirectToRoute('team');
        }

        $queryBuilder = $trainingCampRepository->findAllWithQueryBuilder();

        $trainingCamps = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'team/list_training_camp.html.twig',
            'team/lightmode/list_training_camp.html.twig'
        ), [
            'camps' => $trainingCamps
        ]);
    }

    /**
     * @Route("/join-training-camp/{id}", name="join_training_camp", methods={"GET"})
     *
     * @param PlayerService            $playerService
     * @param int                      $id
     * @param TrainingCampRepository   $trainingCampRepository
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return Response
     * @throws \Exception
     */
    public function joinCamp(
        PlayerService $playerService,
        int $id,
        TrainingCampRepository $trainingCampRepository,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessUnlessGranted(PlayerVoter::HAS_PLAYER);

        $trainingCamp = $trainingCampRepository->find($id);

        if(!$trainingCamp) {
            $this->addFlash('warning', 'admin.training_camp.not_found');

            return $this->redirectToRoute('team_training_camp');
        }

        $price = $trainingCamp->getPrice();

        /**
         * @var Player $player
         */
        $player = $this->getUser()->getPlayer();

        if(!$player->getTeam()) {
            $price = $price + (($price * 25) / 100);
        }

        if($player->getMoney() < $price) {
            $this->addFlash('warning', 'user.no_money');

            return $this->redirectToRoute('team_training_camp');
        }

        if(!$player->isTrainingFinished()) {
            $this->addFlash('warning', 'user.training_camp.in_camp');

            return $this->redirectToRoute('team_training_camp');
        }

        $playerService->improvePlayerAttribute($trainingCamp, $player);

        if ($trainingCamp->getBadge() instanceof Badge) {
            $event = new CreateBadgeForPlayerEvent($player, $trainingCamp->getBadge());
            $eventDispatcher->dispatch($event, CreateBadgeForPlayerEvent::NAME);
        }

        $this->addFlash('success', 'You joined ' . $trainingCamp->getName() . '.');

        return $this->redirectToRoute('team_training_camp');
    }

    /**
     * @Route("/box-score/{id}", name="team_box_score", methods={"GET"})
     *
     * @param int $id
     * @param GameDayRepository $gameDayRepository
     * @param Request $request
     *
     * @return Response
     */
    public function boxScore(int $id, GameDayRepository $gameDayRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $gameDay = $gameDayRepository->find($id);

        if(!$gameDay) {
            $this->addFlash('warning', 'game.not_found');

            return $this->redirectToRoute('team_schedule');
        }

        if($gameDay->getStatus() !== GameDay::STATUS_FINISHED) {
            $this->addFlash('warning', 'Game did not finished or started.');

            return $this->redirectToRoute('team_schedule');
        }

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'team/box_score.html.twig',
            'team/lightmode/box_score.html.twig'
        ), [
            'gameDay' => $gameDay
        ]);
    }
}
