<?php declare(strict_types=1);

namespace App\Form;

use App\Entity\Country;
use App\Entity\GameType;
use App\Entity\Player;
use App\Entity\Position;
use App\Entity\Server;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Vich\UploaderBundle\Form\Type\VichFileType;

class PlayerFormType extends AbstractType
{
    private $em;

    /**
     * PlayerFormType constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entityManager = $this->em;

        $positions = $this->em->getRepository(Position::class)->findAll();
        $countries = $this->em->getRepository(Country::class)->findAll();
        $server = $this->em->getRepository(Server::class)->findAll();
        $gameTypes = $this->em->getRepository(GameType::class)->findAll();

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
                'translation_domain' => 'messages',
            ])
            ->add('weight', IntegerType::class, [
                'label' => 'form.player_type.label.weight',
                'required' => true,
                'attr' => [
                    'min' => Player::MIN_WEIGHT,
                    'max' => Player::MAX_WEIGHT,
                    'value' => Player::MIN_WEIGHT
                ],
                'translation_domain' => 'messages',
            ])
            ->add('height', IntegerType::class, [
                'label' => 'form.player_type.label.height',
                'required' => true,
                'attr' => [
                    'min' => Player::MIN_HEIGHT,
                    'max' => Player::MAX_HEIGHT,
                    'value' => Player::MIN_HEIGHT
                ],
                'translation_domain' => 'messages',
            ])
            ->add('born', DateType::class, [
                'label' => 'form.player_type.label.year',
                'years' => range(1970,2004),
                'required' => true,
                'translation_domain' => 'messages',
            ])
            ->add('position', ChoiceType::class, [
                'label' => 'form.player_type.label.position',
                'required' => true,
                'choices' => $positions,
                'choice_label' => function(Position $position, $key, $value) {
                    return $position->getName();
                },
                'translation_domain' => 'messages',
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'form.player_type.label.country',
                'empty_data' => false,
                'placeholder' => 'form.player_type.placeholder.country',
                'required' => true,
                'choices' => $countries,
                'choice_label' => function(Country $country, $key, $value) {
                    return $country->getName();
                },
                'translation_domain' => 'messages',
            ])
            ->add('server', ChoiceType::class, [
                'label' => 'form.player_type.label.server',
                'required' => true,
                'choices' => $server,
                'choice_label' => function(Server $server, $key, $value) {
                    return $server->getName();
                },
                'translation_domain' => 'messages',
            ])
            ->add('firstType', ChoiceType::class, [
                'label' => 'form.player_type.label.first_type',
                'help' => 'form.player_type.help.first_type',
                'required' => true,
                'choices' => $gameTypes,
                'choice_label' => function(GameType $gameType, $key, $value) {
                    return $gameType->getName();
                },
                'translation_domain' => 'messages',
            ])
            ->add('secondType', ChoiceType::class, [
                'label' => 'form.player_type.label.second_type',
                'help' => 'form.player_type.help.second_type',
                'required' => true,
                'choices' => $gameTypes,
                'choice_label' => function(GameType $gameType, $key, $value) {
                    return $gameType->getName();
                },
                'translation_domain' => 'messages',
            ])
            ->add('imageFile', VichFileType::class, [
                'label' => 'Upload your player picture:',
                'help' => 'Picture should be face of a player or full height player (Players with incorrect images will be deleted or might be banned).',
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
                ],
                'translation_domain' => 'messages',
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $formEvent) use($entityManager) {
                $data = $formEvent->getData();

                $firstName = $data->getFirstName();
                $lastName = $data->getLastName();

                if(!empty($firstName) && !empty($lastName)) {
                    $playerWithSameFirstNameAndLastName = $entityManager->getRepository(Player::class)->findOneBy([
                        'firstname' => $firstName,
                        'lastname' => $lastName
                    ]);

                    if($playerWithSameFirstNameAndLastName) {
                        $formEvent->getForm()->get('firstname')->addError(new FormError('There is already a player with the same name and last name.'));
                        $formEvent->getForm()->get('lastname')->addError(new FormError('There is already a player with the same name and last name.'));
                    }
                }
            })
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Player::class,
        ]);
    }
}
