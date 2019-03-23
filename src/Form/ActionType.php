<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ActionType extends AbstractType 
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder
            ->add('dateFrom', DateType::class, array(
                'label' => false,
                'widget' => 'single_text'                
            ))
            ->add('dateTo', DateType::class, array(
                'label' => false,
                'widget' => 'single_text'                
            ));            
    }

}

