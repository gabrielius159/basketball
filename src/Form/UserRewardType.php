<?php

namespace App\Form;

use App\Entity\UserReward;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UserRewardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mvpAwardName', TextType::class, [
                'label' => 'Reward for MVP:',
                'required' => true,
                'help' => 'Name of the reward for player who won MVP.',
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 100
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Example: Lebron James jersey.'
                ]
            ])
            ->add('imageMvpFile', VichFileType::class, [
                'label' => '*Upload MVP reward image:',
                'required' => true,
                'download_uri' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypesMessage' => 'Please upload a valid image file (jpg, png).'
                    ]),
                ],
                'attr' => [
                    'class' => 'custom-file-input',
                    'id' => 'playerFileUpload',
                    'placeholder' => 'Valid file formats: jpg, jpeg, png.'
                ]
            ])
            ->add('dpoyAwardName', TextType::class, [
                'label' => 'Reward for DPOY:',
                'required' => true,
                'help' => 'Name of the reward for player who won DPOY.',
                'constraints' => [
                    new Length([
                        'min' => 1,
                        'max' => 100
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Example: Kauno Zalgiris membership.'
                ]
            ])
            ->add('imageDpoyFile', VichFileType::class, [
                'label' => '*Upload DPOY reward image:',
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserReward::class,
        ]);
    }
}
