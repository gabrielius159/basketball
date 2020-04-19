<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Coach;
use App\Entity\GameType;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class CoachType extends AbstractType
{
    private $entityManager;

    /**
     * CoachType constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $gameTypes = $this->entityManager->getRepository(GameType::class)->findAll();
        $teams = $this->entityManager->getRepository(Team::class)->createQueryBuilder('t')
            ->leftJoin('t.coach', 'coach')
            ->where('coach IS NULL')
            ->getQuery()
            ->getResult();

        $builder
            ->add('firstname', TextType::class, [
                'label' => 'form.coach_type.label.firstname',
                'required' => true,
                'constraints' => [
                    new Length(
                        [
                            'min' => 3
                        ]
                    )
                ],
                'attr' => [
                    'placeholder' => 'form.coach_type.placeholder.firstname'
                ],
                'translation_domain' => 'messages',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'form.coach_type.label.lastname',
                'required' => true,
                'constraints' => [
                    new Length(
                        [
                            'min' => 3
                        ]
                    )
                ],
                'attr' => [
                    'placeholder' => 'form.coach_type.placeholder.lastname'
                ],
                'translation_domain' => 'messages',
            ])
            ->add('team', ChoiceType::class, [
                'label' => 'form.coach_type.label.team',
                'choices' => $teams,
                'required' => false,
                'empty_data' => null,
                'placeholder' => 'form.coach_type.placeholder.team',
                'choice_label' => function(Team $team, $key, $value) {
                    return $team->getName();
                },
                'translation_domain' => 'messages',
            ])
            ->add('firstGameType', ChoiceType::class, [
                'label' => 'form.coach_type.label.game_type',
                'required' => true,
                'choices' => $gameTypes,
                'choice_label' => function(GameType $gameType, $key, $value) {
                    return $gameType->getName();
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
        $resolver->setDefaults([
            'data_class' => Coach::class,
        ]);
    }
}
