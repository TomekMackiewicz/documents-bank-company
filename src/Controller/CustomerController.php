<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Transfer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

/**
 * @Route("admin/customer")
 */
class CustomerController extends Controller 
{  
    /**
     * Lists all customer entities
     * 
     * @Route("/", name="customer_index", methods={"GET"})
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
     * 
     * @param Request $request
     * 
     * @Route("/new", name="customer_new", methods={"GET","POST"})
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
            
            $this->addFlash('success', 'Customer created');

            return $this->redirectToRoute('customer_show', array('id' => $customer->getId()));
        }
        
        return $this->render('customer/new.html.twig', array(
            'customer' => $customer,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays customer
     * 
     * @param Request $request
     * @Route("/{id}", name="customer_show", methods={"GET","POST"})
     */
    public function showAction(Request $request, Customer $customer) 
    {
        $searchResults = [];

        $em = $this->getDoctrine()->getManager();
        $filesIn  = $em->getRepository('App:Customer')->filesInCountByCustomer($customer->getId());
        $filesOut = $em->getRepository('App:Customer')->filesOutCountByCustomer($customer->getId());
        $transfersForm = $this->createSearchForm();
        $transfersForm->handleRequest($request);

        if ($transfersForm->isSubmitted() && $transfersForm->isValid()) {
            $searchCriteria = $transfersForm->getData();
            $searchCriteria['customer'] = $customer;
            $searchResults = $em->getRepository('App:Transfer')->searchTransfers($searchCriteria); 
        }

        return $this->render('customer/show.html.twig', [
            'customer' => $customer,
            'delete_form' => $this->createDeleteForm($customer)->createView(),
            'filesIn' =>$filesIn,
            'filesOut' =>$filesOut,
            'transfersForm' => $transfersForm->createView(),
            'transfersFromTo' => $searchResults
        ]);
    }

    /**
     * Displays a form to edit customer
     * 
     * @param Request $request
     * @param Customer $customer
     * @Route("/{id}/edit", name="customer_edit", methods={"GET","POST"})
     */
    public function editAction(Request $request, Customer $customer) 
    {
        $deleteForm = $this->createDeleteForm($customer);
        $editForm = $this->createForm('App\Form\CustomerType', $customer);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Customer edited');
            
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
     * @param Request $request
     * @param Customer $customer
     * @Route("/{id}", name="customer_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Customer $customer) 
    {
        $form = $this->createDeleteForm($customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($customer);
            $em->flush($customer);
            
            $this->addFlash('success', 'Customer deleted');
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
            ->add('dateFrom', DateType::class, array(
                'label' => false,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false                
            ))
            ->add('dateTo', DateType::class, array(
                'label' => false,
                'widget' => 'single_text',
                'format' => 'dd-MM-yyyy',
                'html5' => false                
            ))                 
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'In' => Transfer::$transferIn,
                    'Out' => Transfer::$transferOut,
                    'Adjustment' => Transfer::$transferAdjustment
                ],
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'label' => false
            ])
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