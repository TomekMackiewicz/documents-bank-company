<?php

namespace App\Controller;

use App\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;

/**
 * @Route("customer")
 */
class CustomerController extends Controller 
{  
    /**
     * Lists all customer entities
     * 
     * @Route("/", name="customer_index")
     * @Method("GET")
     */
    public function indexAction() 
    {
        $em = $this->getDoctrine()->getManager();
        $customers = $em->getRepository('App:Customer')->excludeAdmin();

        return $this->render('customer/index.html.twig', array(
            'customers' => $customers
        ));
    }

    /**
     * Creates new customer entity
     * @param Request $request
     * 
     * @Route("/new", name="customer_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request) 
    {
        $customer = new Customer();
        $form = $this->createForm('App\Form\CustomerType', $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($customer);
            $em->flush($customer);

            return $this->redirectToRoute('customer_show', array('id' => $customer->getId()));
        }
        
        return $this->render('customer/index.html.twig', array(
            'customer' => $customer,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays customer
     * 
     * @param Request $request
     * @Route("/{id}", name="customer_show")
     * @Method({"GET", "POST"})
     */
    public function showAction(Request $request, Customer $customer) 
    {
        $searchResults = [];
        $actionsFromTo = null;
        $datesError = null;
        $result = null;

        $em = $this->getDoctrine()->getManager(); 
        $repo = $this->getDoctrine()->getRepository('App:Action');
        $filesIn  = $em->getRepository('App:Customer')->filesInCountByCustomer($customer->getId());
        $filesOut = $em->getRepository('App:Customer')->filesOutCountByCustomer($customer->getId());
        $actionsForm = $this->createForm('App\Form\ActionType');
        $actionsForm->handleRequest($request);

        //$queryBuilder = $em->getRepository('App:File')->createQueryBuilder('e');
        $searchForm = $this->createSearchForm();
        $searchForm->handleRequest($request);  

        if ($actionsForm->isSubmitted() && $actionsForm->isValid()) {
            $dateFrom = $actionsForm["dateFrom"]->getData()->format('Y-m-d');
            $dateTo = $actionsForm["dateTo"]->getData()->format('Y-m-d');
            if( strtotime($dateFrom) < strtotime($dateTo) ) {
              $actionsFromTo = $em->getRepository('App:Action')
                ->customerActionsFromTo($customer->getId(), $dateFrom, $dateTo);
            } else {
                $datesError = "Start value can't be higher than end date!";
            }
        }

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchCriteria = $searchForm->getData();
            $em = $this->getDoctrine()->getManager();
            $searchResults = $em->getRepository('App:File')->searchFiles($searchCriteria);             
        }   

        return $this->render('customer/show.html.twig', [
            'customer' => $customer,
            'delete_form' => $this->createDeleteForm($customer)->createView(),
            'filesIn' =>$filesIn,
            'filesOut' =>$filesOut,
            'actionsForm' => $actionsForm->createView(),
            'actionsFromTo' => $actionsFromTo,
            'datesError' => $datesError,
            'filterForm' => $searchForm->createView(),
            'searchResults' => $searchResults
        ]);
    }

    /**
     * Displays a form to edit customer
     * 
     * @param Request $request
     * @param Customer $customer
     * @Route("/{id}/edit", name="customer_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Customer $customer) 
    {
        $deleteForm = $this->createDeleteForm($customer);
        $editForm = $this->createForm('App\Form\CustomerType', $customer);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('customer_show', array('id' => $customer->getId()));
        }

        return $this->render('customer/edit.html.twig', array(
            'customer' => $customer,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ));
    }

    /**
     * Delete customer entity
     * 
     * @Route("/{id}", name="customer_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Customer $customer) 
    {
        $form = $this->createDeleteForm($customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($customer);
            $em->flush($customer);
        }
        
        return $this->redirectToRoute('customer_index');
    }

    /**
     * Create search form
     * 
     * @return Form
     */
    private function createSearchForm()
    {
        return $this->createFormBuilder(null)
            ->add('signature', SearchType::class, [
                'required' => false
            ])
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'In' => 'In',
                    'Out' => 'Out',
                    'Unknown' => 'Unknown'
                ],
                'expanded' => true,
                'multiple' => true
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
                'expanded' => true,
                'multiple' => true
            ])                
            ->add('search', SubmitType::class)
            ->getForm();        
    }     
    
    /**
     * Form to delete a customer entity
     * 
     * @param Customer
     * @return Form
     */
    private function createDeleteForm(Customer $customer) 
    {
        return $this->createFormBuilder()
          ->setAction($this->generateUrl('customer_delete', array('id' => $customer->getId())))
          ->setMethod('DELETE')
          ->getForm();
    }

}