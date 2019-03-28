<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;
use App\Entity\File;

class FileType extends AbstractType 
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) 
    {
        $builder
            ->add('signature', TextType::class, [
                'label' => false
            ])
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'In' => File::$statusIn,
                    'Out' => File::$statusOut,
                    'Unknown' => File::$statusUnknown
                ],
                'label' => false
            ])
            ->add('note', TextareaType::class, [
                'label' => false,
                'required' => false
            ])                
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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) 
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\File'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() 
    {
        return 'app_file';
    }

}
