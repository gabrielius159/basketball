<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Attribute;
use App\Entity\Badge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class BadgeType extends AbstractType
{
    private $entityManager;

    /**
     * BadgeType constructor.
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
        $attributes = $this->entityManager->getRepository(Attribute::class)->findAll();

        $builder
            ->add('name', TextType::class, [
                'label' => 'form.badge_type.label.name',
                'required' => true,
                'constraints' => [
                    new Length(
                        [
                            'min' => 3
                        ]
                    )
                ],
                'attr' => [
                    'placeholder' => 'form.badge_type.placeholder.name'
                ],
                'translation_domain' => 'messages',
            ])
            ->add('attribute', ChoiceType::class, [
                'label' => 'form.badge_type.label.attribute',
                'required' => true,
                'choices' => $attributes,
                'choice_label' => function(Attribute $attribute, $key, $value) {
                    return $attribute->getName();
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
            'data_class' => Badge::class,
        ]);
    }
}
