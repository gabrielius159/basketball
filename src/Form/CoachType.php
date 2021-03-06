<?php

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
    /**
     * @var EntityManagerInterface
     */
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
                'label' => '*Firstname:',
                'required' => true,
                'constraints' => [
                    new Length(
                        [
                            'min' => 3
                        ]
                    )
                ],
                'attr' => [
                    'placeholder' => 'Example: "Coach"'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => '*Lastname:',
                'required' => true,
                'constraints' => [
                    new Length(
                        [
                            'min' => 3
                        ]
                    )
                ],
                'attr' => [
                    'placeholder' => 'Example: "Carter"'
                ]
            ])
            ->add('team', ChoiceType::class, [
                'label' => '*Select team for Head Coach:',
                'choices' => $teams,
                'required' => false,
                'empty_data' => null,
                'placeholder' => 'No Team',
                'choice_label' => function(Team $team, $key, $value) {
                    return $team->getName();
                }
            ])
            ->add('firstGameType', ChoiceType::class, [
                'label' => 'Coach specialization:',
                'required' => true,
                'choices' => $gameTypes,
                'choice_label' => function(GameType $gameType, $key, $value) {
                    return $gameType->getName();
                }
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
