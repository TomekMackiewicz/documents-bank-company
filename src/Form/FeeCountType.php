<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class FeeCountType extends AbstractType 
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder 
            ->add('month', DateType::class, array(
                'widget' => 'single_text',
                'format' => 'MM-yyyy',
                'label' => false,
                'html5' => false
            ))
            ->add('customer', EntityType::class, [
                'class' => 'App:Customer',
                'choice_label' => 'company',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.company', 'ASC')
                        ->where('c.roles NOT LIKE :roles')
                        ->setParameter('roles', '%ADMIN%');
                },
                'label' => false
            ]);                        
    }

}