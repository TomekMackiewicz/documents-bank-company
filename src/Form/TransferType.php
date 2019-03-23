<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Doctrine\ORM\EntityRepository;
use App\Entity\Transfer;
use App\Form\DataTransformer\StringToFileTransformer;

class TransferType extends AbstractType 
{
    private $transformer;

    public function __construct(StringToFileTransformer $transformer)
    {
        $this->transformer = $transformer;
    }    
    
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
                ],
                'label' => false
            ])                
            ->add('files', TextType::class, [
                'label' => false
            ])
            ->add('customer', EntityType::class, [
                'class' => 'App:Customer',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC')
                        ->where('c.roles NOT LIKE :roles')
                        ->setParameter('roles', '%ROLE_ADMIN%');
                },
                'label' => false
            ])
            ->add('date', DateType::class, array(
                'label' => 'Date',
                'widget' => 'single_text',
                'label' => false                
            ));
            
        $builder->get('files')->addModelTransformer($this->transformer);
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
