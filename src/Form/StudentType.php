<?php

namespace App\Form;

use App\Entity\SchoolClass;
use App\Entity\Subject;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StudentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class);

        if ($options['is_edit'] == false) 
        {
            $builder->add('password', PasswordType::class);
        }

        $builder
            ->add('class', EntityType::class,
            [
                'class' => SchoolClass::class,
                'choice_label' => 'name',
            ])
            ->add("save", SubmitType::class);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            "is_edit" => false
        ]);
    }
}
