<?php

namespace App\Form;

use App\Entity\Search;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('latitude', NumberType::class, [
                'scale' => 6,              // ou null si tu veux laisser libre
                'rounding_mode' => 6,    // PAS d’arrondi
                'input' => 'string',        // très important
                'html5' => false,
                'attr' => [
                    'step' => 'any',
                ],
            ])
            ->add('longitude', NumberType::class, [
                'scale' => 6,              // ou null si tu veux laisser libre
                'rounding_mode' => 6,    // PAS d’arrondi
                'input' => 'string',        // très important
                'html5' => false,
                'attr' => [
                    'step' => 'any',
                ],
            ])
            ->add('city')
            ->add('save', SubmitType::class, ['label' => 'Rechercher'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Search::class,
        ]);
    }
}
