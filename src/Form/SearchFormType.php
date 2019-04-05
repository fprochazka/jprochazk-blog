<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Length;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): self
    {
        $builder
            ->add('query', SearchType::class, [
                'required' => true,
                'constraints' => [
                    new Length([
                        'min' => 3,
                        'max' => 10
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Search'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => '🡆', 
                'attr' => ['class' => 'search-submit']
            ])
        ;

        return $this;
    }

    public function configureOptions(OptionsResolver $resolver): self
    {
        return $this;
    }
}
