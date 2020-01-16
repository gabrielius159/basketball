<?php

namespace App\Form;

use App\Entity\Attribute;
use App\Entity\GameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Class AttributeType
 *
 * @package App\Form
 */
class AttributeType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * AttributeType constructor.
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
        $gameTypeRepository = $this->entityManager->getRepository(GameType::class);
        $gameTypes = $gameTypeRepository->findAll();

        $builder
            ->add('name', TextType::class, [
                'label' => '*Attribute name:',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 2
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Example: "Speed"'
                ]
            ])
            ->add('defaultValue', IntegerType::class, [
                'label' => '*Default attribute value:',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'value' => 25
                ],
                'help' => 'This value will be applied for all players [Range: 0 - 100].'
            ])
            ->add('gameType', ChoiceType::class, [
                'label' => 'Skill category:',
                'required' => true,
                'choices' => $gameTypes,
                'choice_label' => function(GameType $gameType) {
                    return $gameType->getName();
                }
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Attribute::class,
        ]);
    }
}
