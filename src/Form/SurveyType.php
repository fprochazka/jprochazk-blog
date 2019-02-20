<?php

namespace App\Form;

use App\Entity\Survey;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SurveyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder
            ->add('title', TextareaType::class)
            ->add('options', CollectionType::class, [
                'entry_type' => SurveyOptionType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
            ])
            ->add('save', SubmitType::class, ['label' => 'Submit'])
        ;

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        $resolver->setDefaults([
            'data_class' => Survey::class,
        ]);

        return $this;
    }
}
