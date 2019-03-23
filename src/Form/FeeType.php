<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityRepository;

class FeeType extends AbstractType {
  /**
   * {@inheritdoc}
   */
  public function buildForm(FormBuilderInterface $builder, array $options) {
    $builder
      ->add('delivery', TextType::class, array(
        'label' => 'Delivery Price'
      ))
      ->add('import', TextType::class, array(
        'label' => 'Import Price'
      ))
      ->add('storage', TextType::class, array(
        'label' => 'Storage Price'
      ))
      ->add('customer', EntityType::class,[
        'class' => 'App:Customer',
        'choice_label' => 'name',
        'query_builder' => function (EntityRepository $er) {
          return $er->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->where('c.roles NOT LIKE :roles')
            ->setParameter('roles', '%ROLE_ADMIN%');
        }
      ]);
  }
  
  /**
   * {@inheritdoc}
   */
  public function configureOptions(OptionsResolver $resolver) {
    $resolver->setDefaults(array(
        'data_class' => 'App\Entity\Fee'
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockPrefix() {
    return 'app_fee';
  }

}