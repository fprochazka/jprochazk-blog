<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class SurveyDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options = null)
    {
        $builder
            ->add('submit', SubmitType::class, [
                'label' => 'DELETE',
                'attr' => ['class' => 'delete-survey']
            ])
        ;
    }
}
