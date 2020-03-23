<?php

namespace App\Controller;

use App\Entity\Attribute;
use App\Entity\Badge;
use App\Entity\Coach;
use App\Entity\Player;
use App\Entity\Season;
use App\Entity\Server;
use App\Entity\Team;
use App\Entity\TrainingCamp;
use App\Entity\UserReward;
use App\Event\CreatePlayerAttributeForPlayersEvent;
use App\Form\AttributeType;
use App\Form\BadgeType;
use App\Form\ChangeCoachType;
use App\Form\CoachType;
use App\Form\SeasonStartFormType;
use App\Form\TeamType;
use App\Form\TrainingCampType;
use App\Form\UserRewardType;
use App\Message\SimulateGames;
use App\Message\SimulateOneGame;
use App\Message\SimulateTwoGames;
use App\Message\StartSeason;
use App\Repository\AttributeRepository;
use App\Repository\BadgeRepository;
use App\Repository\CoachRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Repository\TrainingCampRepository;
use App\Repository\UserRepository;
use App\Service\Admin\SeasonManagementService;
use App\Service\AttributeService;
use App\Service\BadgeService;
use App\Service\CoachService;
use App\Service\PlayerService;
use App\Service\SeasonService;
use App\Service\ServerService;
use App\Service\TeamService;
use App\Service\TeamStatusService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/admin")
 */
class AdminController extends BaseController
{
    /**
     * @Route("/dashboard", name="admin_dashboard", methods={"GET"})
     *
     * @return Response
     */
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('admin/dashboard.html.twig');
    }

    /**
     * @Route("/new-attribute", name="new_attribute", methods={"GET", "POST"})
     *
     * @param Request                  $request
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return Response
     */
    public function createAttribute(
        Request $request,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $attribute = new Attribute();

        $form = $this->createForm(AttributeType::class, $attribute);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($attribute);
            $em->flush();

            $event = new CreatePlayerAttributeForPlayersEvent($attribute);
            $eventDispatcher->dispatch($event, CreatePlayerAttributeForPlayersEvent::NAME);

            $this->addFlash('success', 'Attribute created successfully.');

            return $this->redirectToRoute('new_attribute');
        }

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'admin/new_attribute.html.twig',
            'admin/lightmode/new_attribute.html.twig'
        ), [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }

    /**
     * @Route("/attributes", name="list_attribute", methods={"GET"})
     *
     * @param Request $request
     * @param AttributeRepository $attributeRepository
     * @param PaginatorInterface $paginator
     *
     * @return Response
     */
    public function listAttribute(
        Request $request,
        AttributeRepository $attributeRepository,
        PaginatorInterface $paginator
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $queryBuilder = $attributeRepository->findAllWithQueryBuilder();

        $attributes = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render($this->getTemplateByTemplateMode($request, 'admin/list_attribute.html.twig', 'admin/lightmode/list_attribute.html.twig'), [
           'attributes' => $attributes
        ]);
    }

    /**
     * @Route("/attributes/delete/{id}", name="delete_attribute")
     *
     * @param AttributeRepository $attributeRepository
     * @param int $id
     *
     * @return Response
     */
    public function deleteAttribute(AttributeRepository $attributeRepository, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $attribute = $attributeRepository->find($id);

        if(!$attribute) {
            $this->addFlash('warning', 'Attribute couldn\'t be found.');

            return $this->redirectToRoute('list_attribute');
        }

        $this->getDoctrine()->getManager()->remove($attribute);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', 'Attribute deleted successfully.');

        return $this->redirectToRoute('list_attribute');
    }

    /**
     * @Route("/new-team", name="new_team", methods={"GET", "POST"})
     *
     * @param TeamStatusService $teamStatusService
     * @param Request $request
     * @param ServerService $serverService
     * @param SeasonService $seasonService
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createTeam(
        TeamStatusService $teamStatusService,
        Request $request,
        ServerService $serverService,
        SeasonService $seasonService
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $team = new Team();

        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if($seasonService->getActiveSeason($serverService->getCurrentServer())->getStatus() == Season::STATUS_ACTIVE) {
                $this->addFlash('warning', 'You can\'t create team when season is in progress.');

                return $this->redirectToRoute('new_team');
            }

            $em = $this->getDoctrine()->getManager();

            $em->persist($team);
            $em->flush();

            $teamStatusService->createTeamStatusOnTeamCreate($team);
            $this->addFlash('success', 'Team created successfully.');

            return $this->redirectToRoute('new_team');
        }

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'admin/new_team.html.twig',
            'admin/lightmode/new_team.html.twig'
        ), [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }

    /**
     * @Route("/teams", name="list_team", methods={"GET"})
     *
     * @param Request $request
     * @param TeamRepository $teamRepository
     * @param PaginatorInterface $paginator
     *
     * @return Response
     */
    public function listTeam(
        Request $request,
        TeamRepository $teamRepository,
        PaginatorInterface $paginator
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $queryBuilder = $teamRepository->findAllWithQueryBuilder();

        $teams = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'admin/list_team.html.twig',
            'admin/lightmode/list_team.html.twig'), [
           'teams' => $teams
        ]);
    }

    /**
     * @Route("/teams/delete/{id}", name="delete_team")
     *
     * @param TeamService $teamService
     * @param int $id
     *
     * @return Response
     */
    public function deleteTeam(TeamService $teamService, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $team = $teamService->findOneById($id);

        if(!$team) {
            $this->addFlash('warning', 'Team couldn\'t be found.');

            return $this->redirectToRoute('list_team');
        }

        $teamService->deleteTeam($team);
        $this->addFlash('success', 'Team deleted successfully!');

        return $this->redirectToRoute('list_team');
    }

    /**
     * @Route("/new-coach", name="new_coach", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createCoach(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $coach = new Coach();

        $form = $this->createForm(CoachType::class, $coach);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($coach);
            $em->flush();

            $this->addFlash('success', 'Coach created successfully.');

            return $this->redirectToRoute('new_coach');
        }

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'admin/new_coach.html.twig',
            'admin/lightmode/new_coach.html.twig'
        ), [
           'form' => $form->createView(),
           'errors' => $form->getErrors()
        ]);
    }

    /**
     * @Route("/coaches", name="list_coach", methods={"GET"})
     *
     * @param Request $request
     * @param CoachRepository $coachRepository
     * @param PaginatorInterface $paginator
     *
     * @return Response
     */
    public function listCoach(
        Request $request,
        CoachRepository $coachRepository,
        PaginatorInterface $paginator
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $queryBuilder = $coachRepository->findAllWithQueryBuilder();

        $coaches = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render($this->getTemplateByTemplateMode($request, 'admin/list_coach.html.twig', 'admin/lightmode/list_coach.html.twig'), [
            'coaches' => $coaches
        ]);
    }

    /**
     * @Route("/coaches/delete/{id}", name="delete_coach")
     *
     * @param CoachService $coachService
     * @param int $id
     *
     * @return Response
     */
    public function deleteCoach(CoachService $coachService, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $coach = $coachService->findOneById($id);

        if(!$coach) {
            $this->addFlash('warning', 'Coach couldn\'t be found.');

            return $this->redirectToRoute('list_coach');
        }

        if($coach->getTeam() instanceof Team) {
            $this->addFlash('warning', 'First find set new coach for the team.');

            return $this->redirectToRoute('list_coach');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($coach);
        $em->flush();

        $this->addFlash('success', 'Coach deleted successfully.');

        return $this->redirectToRoute('list_coach');
    }

    /**
     * @Route("/new-badge", name="new_badge", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createBadge(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $badge = new Badge();
        $form = $this->createForm(BadgeType::class, $badge);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($badge);
            $em->flush();

            $this->addFlash('success', 'Badge created successfully.');

            return $this->redirectToRoute('new_badge');
        }

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'admin/new_badge.html.twig',
            'admin/lightmode/new_badge.html.twig'
        ), [
           'form' => $form->createView(),
           'errors' => $form->getErrors()
        ]);
    }

    /**
     * @Route("/badges", name="list_badge", methods={"GET"})
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param BadgeRepository $badgeRepository
     *
     * @return Response
     */
    public function listBadge(
        Request $request,
        PaginatorInterface $paginator,
        BadgeRepository $badgeRepository
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $queryBuilder = $badgeRepository->findAllWithQueryBuilder();

        $badges = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            15
        );

        return $this->render($this->getTemplateByTemplateMode($request, 'admin/list_badge.html.twig', 'admin/lightmode/list_badge.html.twig'), [
           'badges' => $badges
        ]);
    }

    /**
     * @Route("/badges/delete/{id}", name="delete_badge")
     *
     * @param BadgeService $badgeService
     * @param int $id
     *
     * @return Response
     */
    public function deleteBadge(BadgeService $badgeService, int $id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $badge = $badgeService->findOneById($id);

        if(!$badge) {
            $this->addFlash('warning', 'Badge couldn\'t be found.');

            return $this->redirectToRoute('list_badge');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($badge);
        $em->flush();

        $this->addFlash('success', 'Badge deleted successfully.');

        return $this->redirectToRoute('list_badge');
    }

    /**
     * @Route("/players", name="list_player", methods={"GET"})
     *
     * @param PaginatorInterface $paginator
     * @param PlayerRepository $playerRepository
     * @param Request $request
     *
     * @return Response
     */
    public function listPlayer(
        PaginatorInterface $paginator,
        PlayerRepository $playerRepository,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $keyword = $request->query->get('search');

        $queryBuilder = $playerRepository->findAllWithQueryBuilder($keyword ? $keyword : null);

        $players = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'admin/list_player.html.twig',
            'admin/lightmode/list_player.html.twig'), [
            'players' => $players
        ]);
    }

    /**
     * @Route("/players/delete-all", name="delete_players")
     *
     * @param PlayerRepository $playerRepository
     * @param ServerService $serverService
     * @param SeasonService $seasonService
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function deletePlayers(
        PlayerRepository $playerRepository,
        ServerService $serverService,
        SeasonService $seasonService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $server = $serverService->getCurrentServer();
        $season = $seasonService->getActiveSeason($server);

        if($season->getStatus() == Season::STATUS_ACTIVE) {
            $this->addFlash('warning', 'You can\'t delete all players right now, let season finish.');

            return $this->redirectToRoute('list_player');
        }

        $em = $this->getDoctrine()->getManager();

        $fakePlayer = $playerRepository->findAllForDelete(false);

        $realPlayers = $playerRepository->findAllForDelete();

        $numberOfChunks = intval(ceil(count($fakePlayer) / 1000));
        $chunks = array_chunk($fakePlayer, $numberOfChunks);

        foreach($chunks as $chunk) {
            /**
             * @var Player $player
             */
            foreach($chunk as $player) {
                if(count($player->getPlayerAwards()) > 0) {
                    foreach($player->getPlayerAwards() as $playerAward) {
                        $em->remove($playerAward);
                        $em->flush();
                    }
                }

                $em->remove($player);
            }

            $em->flush();
        }


        foreach($realPlayers as $player) {
            if(count($player->getPlayerAwards()) > 0) {
                foreach($player->getPlayerAwards() as $playerAward) {
                    $em->remove($playerAward);
                    $em->flush();
                }
            }

            $em->remove($player);
            $em->flush();
        }

        $this->addFlash('success', 'Players deleted successfully.');

        return $this->redirectToRoute('list_player');
    }

    /**
     * @Route("/players/delete/{id}", name="delete_player")
     *
     * @param int $id
     * @param PlayerRepository $playerRepository
     * @param SeasonService $seasonService
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function deletePlayer(
        int $id,
        PlayerRepository $playerRepository,
        SeasonService $seasonService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $player = $playerRepository->find($id);

        if(!$player) {
            $this->addFlash('warning', 'Player couldn\'t be found.');

            return $this->redirectToRoute('list_player');
        }

        if($seasonService->getActiveSeason($player->getServer())->getStatus() === Season::STATUS_ACTIVE
            && $player->getTeam() != null
            && (count($player->getTeam()->getPlayers()) - 1) < 10) {
            $seasonService->createFakePlayer($player->getTeam(), $player->getPosition()->getName(), 1);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($player);
        $em->flush();

        $this->addFlash('success', 'Player deleted successfully.');

        return $this->redirectToRoute('list_player');
    }

    /**
     * @Route("/season", name="admin_season", methods={"GET", "POST"})
     *
     * @param Request                 $request
     * @param ServerService           $serverService
     * @param SeasonService           $seasonService
     * @param SeasonManagementService $seasonManagementService
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function season(
        Request $request,
        ServerService $serverService,
        SeasonService $seasonService,
        SeasonManagementService $seasonManagementService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(SeasonStartFormType::class);
        $form->handleRequest($request);

        $server = $serverService->getCurrentServer();
        $season = $seasonService->getActiveSeason($server);

        if($form->isSubmitted() && $form->isValid()) {
            $status = $form->getData()['status'];

            list($key, $message) = $seasonManagementService->dispatchChoosenActionAndReturnMessage($status, $season);
            $this->addFlash($key, $message);

            return $this->redirectToRoute('admin_season');
        }

        return $this->render('admin/season.html.twig', [
            'form' => $form->createView(),
            'seasonStatus' => $seasonService->getSeasonStatusName($seasonService->getActiveSeason($server)->getStatus())
        ]);
    }

    /**
     * @Route("/new-training-camp", name="new_training_camp", methods={"GET", "POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createTrainingCamp(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $trainingCamp = new TrainingCamp();

        $form = $this->createForm(TrainingCampType::class, $trainingCamp);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($trainingCamp);
            $em->flush();

            $this->addFlash('success', 'Training camp created.');

            return $this->redirectToRoute('new_training_camp');
        }

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'admin/new_training_camp.html.twig',
            'admin/lightmode/new_training_camp.html.twig'
        ), [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }

    /**
     * @Route("/training-camps", name="list_training_camp", methods={"GET"})
     *
     * @param Request $request
     * @param TrainingCampRepository $trainingCampRepository
     * @param PaginatorInterface $paginator
     *
     * @return Response
     */
    public function listTrainingCamp(
        Request $request,
        TrainingCampRepository $trainingCampRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $queryBuilder = $trainingCampRepository->findAllWithQueryBuilder();

        $trainingCamps = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'admin/list_training_camp.html.twig',
            'admin/lightmode/list_training_camp.html.twig'
        ), [
           'camps' => $trainingCamps
        ]);
    }

    /**
     * @Route("/delete-training-camp/{id}", name="delete_training_camp")
     *
     * @param int $id
     * @param TrainingCampRepository $trainingCampRepository
     *
     * @return Response
     */
    public function deleteTrainingCamp(int $id, TrainingCampRepository $trainingCampRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $trainingCamp = $trainingCampRepository->find($id);

        if(!$trainingCamp) {
            $this->addFlash('warning', 'Training camp was not found.');

            return $this->redirectToRoute('list_training_camp');
        }

        foreach($trainingCamp->getPlayers() as $player) {
            $player->setTrainingFinishes(null);
            $player->setCamp(null);
        }

        $em = $this->getDoctrine()->getManager();

        $em->remove($trainingCamp);
        $em->flush();

        $this->addFlash('success', 'Training camp was deleted.');

        return $this->redirectToRoute('list_training_camp');
    }

    /**
     * @Route("/users", name="list_user", methods={"GET"})
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param PaginatorInterface $paginator
     *
     * @return Response
     */
    public function listUser(Request $request, UserRepository $userRepository, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $searchParam = $request->query->get('search');

        $queryBuilder = $userRepository->findAllWithQueryBuilder($searchParam ? $searchParam : null);

        $users = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'admin/list_user.html.twig',
            'admin/lightmode/list_user.html.twig'
        ), [
            'users' => $users
        ]);
    }

    /**
     * @Route("/set-rewards", name="set_user_rewards", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param ServerService $serverService
     * @param SeasonService $seasonService
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createUserReward(
        Request $request,
        ServerService $serverService,
        SeasonService $seasonService
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $server = $serverService->getCurrentServer();
        $season = $seasonService->getActiveSeason($server);

        if($season->getUserReward()) {
            $this->addFlash('warning', 'Rewards for current season are already set.');

            return $this->redirectToRoute('admin_dashboard');
        }

        $userReward = (new UserReward())
            ->setSeason($season);

        $form = $this->createForm(UserRewardType::class, $userReward);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($userReward);
            $em->flush();

            $this->addFlash('success', 'Rewards set successfully.');

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render($this->getTemplateByTemplateMode(
            $request,
            'admin/new_user_reward.html.twig',
            'admin/lightmode/new_user_reward.html.twig'
        ), [
            'form' => $form->createView(),
            'errors' => $form->getErrors()
        ]);
    }

    /**
     * @Route("/change-coach", name="admin_change_coach", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param CoachRepository $coachRepository
     *
     * @return Response
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function changeCoach(
        Request $request,
        CoachRepository $coachRepository
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $form = $this->createForm(ChangeCoachType::class);
        $form->handleRequest($request);

        if($coachRepository->countFreeAgentCoaches() < 1 && $request->getMethod() === Request::METHOD_GET) {
            $this->addFlash('warning', 'There is no coaches to be changed.');

            return $this->redirectToRoute('admin_dashboard');
        }

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /**
             * @var Team $team
             */
            $team = $em->getRepository(Team::class)->find($form->getData()['team']);
            /**
             * @var Coach $coach
             */
            $coach = $em->getRepository(Coach::class)->find($form->getData()['coach']);

            if($team->getCoach()) {
                $oldCoach = $team->getCoach();
                $oldCoach->setTeam(null);

                $this->addFlash('success', 'Coach changed successfully.');
            } else {
                $this->addFlash('success', 'Great, team now has coach.');
            }

            $team->setCoach($coach);
            $em->flush();

            return $this->redirectToRoute('list_coach');
        }

        return $this->render('admin/change_coach.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
