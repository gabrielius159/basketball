<?php

namespace App\Form;

use App\Entity\Season;
use App\Utils\Season as SeasonUtil;
use App\Service\SeasonService;
use App\Service\ServerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SeasonStartFormType
 *
 * @package App\Form
 */
class SeasonStartFormType extends AbstractType
{
    const SIMULATE_ONE_GAME = 1;
    const SIMULATE_TWO_GAME = 2;
    const SIMULATE_SEASON = 3;
    const START_SEASON = 4;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ServerService
     */
    private $serverService;

    /**
     * @var SeasonService
     */
    private $seasonService;

    /**
     * SeasonStartFormType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ServerService $serverService
     * @param SeasonService $seasonService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ServerService $serverService,
        SeasonService $seasonService
    ) {
        $this->entityManager = $entityManager;
        $this->serverService = $serverService;
        $this->seasonService = $seasonService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $server = $this->serverService->getCurrentServer();
        $season = $this->seasonService->getActiveSeason($server);

        if($season->getStatus() === Season::STATUS_ACTIVE) {
            $builder
                ->add('status', ChoiceType::class, [
                    'label' => 'Select action:',
                    'required' => true,
                    'empty_data' => false,
                    'choices' => [self::SIMULATE_ONE_GAME, self::SIMULATE_TWO_GAME, self::SIMULATE_SEASON],
                    'choice_label' => function($choices, $key, $value) {
                        switch ($value) {
                            case self::SIMULATE_ONE_GAME: {
                                return 'Simulate one game';
                            }
                            case self::SIMULATE_TWO_GAME: {
                                return 'Simulate two games';
                            }
                            case self::SIMULATE_SEASON: {
                                return 'Simulate season';
                            }
                        }

                        return null;
                    },
                ])
            ;
        } else {
            $builder
                ->add('status', ChoiceType::class, [
                    'label' => 'Select action:',
                    'required' => true,
                    'choices' => [self::START_SEASON],
                    'choice_label' => function () {
                        return 'Start season';
                    },
                ])
            ;
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
