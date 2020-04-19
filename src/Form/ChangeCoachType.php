<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Coach;
use App\Entity\Team;
use App\Service\SeasonService;
use App\Service\ServerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeCoachType extends AbstractType
{
    private $serverService;
    private $entityManager;

    /**
     * ChangeCoachType constructor.
     *
     * @param ServerService $serverService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ServerService $serverService, EntityManagerInterface $entityManager)
    {
        $this->serverService = $serverService;
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $teams = $this->entityManager->getRepository(Team::class)->findBy([
            'server' => $this->serverService->getCurrentServer()
        ]);

        $coaches = $this->entityManager->getRepository(Coach::class)->findBy([
            'team' => null
        ]);

        $builder
            ->add('team', ChoiceType::class, [
                'label' => 'form.change_coach_type.label.team',
                'choices' => $teams,
                'choice_label' => function(Team $team) {
                    if(!$team->getCoach()) {
                        return $team->getCity() . ' ' . $team->getName() . ' (No coach)';
                    }

                    return $team->getCity() . ' ' . $team->getName() . ' (' . $team->getCoach()->getFirstname() . ' ' .
                        $team->getCoach()->getLastname() . ', type: ' .
                        $team->getCoach()->getFirstGameType()->getName() . ')';
                },
                'translation_domain' => 'messages',
            ])
            ->add('coach', ChoiceType::class, [
                'label' => 'form.change_coach_type.label.coach',
                'choices' => $coaches,
                'choice_label' => function(Coach $coach) {
                    return $coach->getFirstname() . ' ' . $coach->getLastname() . ' (Type: ' . $coach->getFirstGameType()->getName() . ')';
                },
                'translation_domain' => 'messages',
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
