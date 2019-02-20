<?php

namespace App\Form;

use App\Entity\SurveyOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class SurveyOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder->add('title', TextType::class);

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        $resolver->setDefaults([
            'data_class' => SurveyOption::class,
        ]);

        return $this;
    }
}
