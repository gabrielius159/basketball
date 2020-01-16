<?php

namespace App\Form;

use App\Entity\Server;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Vich\UploaderBundle\Form\Type\VichFileType;

class TeamType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * TeamType constructor.
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
        $servers = $this->entityManager->getRepository(Server::class)->findAll();

        $builder
            ->add('city', TextType::class, [
                'label' => '*Team city:',
                'required' => true,
                'constraints' => [
                    new Length(
                        [
                            'min' => 3
                        ]
                    )
                ],
                'attr' => [
                    'placeholder' => 'Example: "Richmond"'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => '*Team name:',
                'required' => true,
                'constraints' => [
                    new Length(
                        [
                            'min' => 3
                        ]
                    )
                ],
                'attr' => [
                    'placeholder' => 'Example: "Lions"'
                ]
            ])
            ->add('budget', MoneyType::class, [
                'label' => '*Team salary cap:',
                'required' => true,
                'currency' => 'USD',
                'attr' => [
                    'min' => 0,
                    'step' => 0.01,
                    'value' => rand(30000, 40000)
                ],
                'help' => 'How much team can spend on players?'
            ])
            ->add('server', ChoiceType::class, [
                'label' => 'Pick a server:',
                'required' => true,
                'choices' => $servers,
                'choice_label' => function(Server $server, $key, $value) {
                    return $server->getName();
                }
            ])
            ->add('imageFile', VichFileType::class, [
                'label' => '*Upload team logo:',
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
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
