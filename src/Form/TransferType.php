<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Doctrine\ORM\EntityRepository;
use App\Entity\Transfer;

class TransferType extends AbstractType 
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'In' => Transfer::$transferIn,
                    'Out' => Transfer::$transferOut
                ]
            ])
            ->add('files', EntityType::class,[
                'class' => 'App:File',
                'choice_label' => 'signature',
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('f')
                        ->orderBy('f.signature', 'ASC');
                }
            ])
            ->add('customer', EntityType::class,[
                'class' => 'App:Customer',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC')
                        ->where('c.roles NOT LIKE :roles')
                        ->setParameter('roles', '%ROLE_ADMIN%');
                }
            ])
            ->add('date', DateType::class, array(
                'label' => 'Date',
                'widget' => 'single_text'                
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) 
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Transfer'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() 
    {
        return 'app_transfer';
    }

}
