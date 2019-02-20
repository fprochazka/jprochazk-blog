<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder
            ->add('title', TextType::class)
            ->add('content', TextareaType::class)
            ->add('save', SubmitType::class, ['label' => 'Submit post'])
        ;

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);

        return $this;
    }
}
