<?php declare(strict_types=1);

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

class AttributeType extends AbstractType
{
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
                'label' => 'form.attribute_type.label.name',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 2
                    ])
                ],
                'translation_domain' => 'messages',
                'attr' => [
                    'placeholder' => 'form.attribute_type.placeholder.name'
                ]
            ])
            ->add('defaultValue', IntegerType::class, [
                'label' => 'form.attribute_type.label.default_value',
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'value' => 25
                ],
                'translation_domain' => 'messages',
                'help' => 'form.attribute_type.help.default_value'
            ])
            ->add('gameType', ChoiceType::class, [
                'label' => 'form.attribute_type.label.game_type',
                'required' => true,
                'choices' => $gameTypes,
                'choice_label' => function(GameType $gameType) {
                    return $gameType->getName();
                },
                'translation_domain' => 'messages',
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
