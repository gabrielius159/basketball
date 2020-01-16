<?php

namespace App\Form;

use App\Entity\Attribute;
use App\Entity\Badge;
use App\Entity\TrainingCamp;
use App\Repository\AttributeRepository;
use App\Repository\BadgeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Vich\UploaderBundle\Form\Type\VichFileType;

/**
 * Class TrainingCampType
 *
 * @package App\Form
 */
class TrainingCampType extends AbstractType
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var BadgeRepository
     */
    private $badgeRepository;

    /**
     * TrainingCampType constructor.
     *
     * @param AttributeRepository $attributeRepository
     * @param BadgeRepository $badgeRepository
     */
    public function __construct(AttributeRepository $attributeRepository, BadgeRepository $badgeRepository)
    {
        $this->attributeRepository = $attributeRepository;
        $this->badgeRepository = $badgeRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $attributes = $this->attributeRepository->findAll();

        $badges = $this->badgeRepository->findAll();

        $builder
            ->add('name', TextType::class, [
                'label' => '*Name of the camp:',
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 3
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Example: "LeBron James passing camp"'
                ],
                'help' => 'Would be nice to include attribute name that will be improved.'
            ])
            ->add('skillPoints', NumberType::class, [
                'label' => '*Skill points',
                'required' => true,
                'help' => 'This value will determine how much skill points will be added to player attribute.',
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 0.01,
                    'value' => 0.5
                ]
            ])
            ->add('attributeToImprove', ChoiceType::class, [
                'label' => 'Skill to improve:',
                'required' => true,
                'choices' => $attributes,
                'choice_label' => function(Attribute $attribute) {
                    return $attribute->getName();
                }
            ])
            ->add('duration', IntegerType::class, [
                'label' => '*Duration (hours):',
                'required' => true,
                'help' => 'How many hours training will take?',
                'attr' => [
                    'min' => 0,
                    'max' => 24,
                    'value' => 2
                ]
            ])
            ->add('price', MoneyType::class, [
                'label' => '*Price:',
                'required' => true,
                'currency' => 'USD',
                'help' => 'Price to this training camp.',
                'attr' => [
                    'min' => 0,
                    'max' => 10000000,
                    'value' => 50215
                ]
            ])
            ->add('imageFile', VichFileType::class, [
                'label' => '*Upload training camp image:',
                'required' => true,
                'download_uri' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ["image/jpeg", "image/jpg", "image/png"],
                        'mimeTypesMessage' => 'Please upload a valid image file (jpg, png).'
                    ]),
                ],
                'attr' => [
                    'class' => 'custom-file-input',
                    'id' => 'playerFileUpload',
                    'placeholder' => 'Valid file formats: jpg, jpeg, png.'
                ]
            ])
            ->add('badge', ChoiceType::class, [
                'label' => 'Badge:',
                'help' => 'It is not required, but training camp can give badge for player.',
                'required' => false,
                'choices' => $badges,
                'empty_data' => null,
                'placeholder' => 'No badge',
                'choice_label' => function(Badge $badge) {
                    return $badge->getName();
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
            'data_class' => TrainingCamp::class,
        ]);
    }
}
