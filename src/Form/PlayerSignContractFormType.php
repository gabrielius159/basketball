<?php

namespace App\Form;

use App\Entity\Player;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerSignContractFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [
            'One year' => Player::CONTRACT_YEAR_ONE,
            'Two year' => Player::CONTRACT_YEAR_TWO,
            'Three year' => Player::CONTRACT_YEAR_THREE,
            'Four year' => Player::CONTRACT_YEAR_FOUR
        ];

        $builder
            ->add('years', ChoiceType::class, [
                'label' => 'Select years:',
                'required' => true,
                'choices' => $choices,
                'choice_label' => function ($choices, $key, $value) {
                    return $key;
                },
            ])
            ->add('Sign contract', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-md btn-warning'
                ]
            ]);
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
