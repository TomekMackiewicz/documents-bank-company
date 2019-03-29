<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Transfer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

/**
 * @Route("admin/customer")
 */
class CustomerController extends AbstractController 
{
    /**
     * Lists all user entities
     * 
     * @Route("/", name="customer_index", methods={"GET"})
     */
    public function indexAction() 
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('App:User')->excludeAdmin();

        return $this->render('customer/index.html.twig', array(
            'users' => $users
        ));
    }

    /**
     * Creates new user entity
     * 
     * @param Request $request
     * 
     * @Route("/new", name="customer_new", methods={"GET","POST"})
     */
    public function newAction(Request $request) 
    {
        $user = new User();
        $user->setEnabled(true);
        $form = $this->createForm('App\Form\CustomerType', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $password = $form['password']->getData();
            $user->setPlainPassword($password);
            
            $em->persist($user);
            $em->flush($user);
            
            $this->addFlash('success', 'Customer created');

            return $this->redirectToRoute('customer_show', array('id' => $user->getId()));
        }
        
        return $this->render('customer/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays user
     * 
     * @param Request $request
     * @Route("/{id}", name="customer_show", methods={"GET","POST"})
     */
    public function showAction(Request $request, User $user) 
    {
        $searchResults = [];

        $em = $this->getDoctrine()->getManager();
        $files = $em->getRepository('App:User')->customerFilesByType($user->getId());
        $transfersForm = $this->createSearchForm();
        $transfersForm->handleRequest($request);

        if ($transfersForm->isSubmitted() && $transfersForm->isValid()) {
            $searchCriteria = $transfersForm->getData();
            $searchCriteria['user'] = $user;            
            $dateFrom = $searchCriteria["dateFrom"]->format('Y-m-d');
            $dateTo = $searchCriteria["dateTo"]->format('Y-m-d');
            if(strtotime($dateFrom) <= strtotime($dateTo)) {
                $searchResults = $em->getRepository('App:Transfer')->searchTransfers($searchCriteria); 
            } else { 
                $this->addFlash('error', "Start value can't be higher than end date");
            }           
        }

        return $this->render('customer/show.html.twig', [
            'user' => $user,
            'delete_form' => $this->createDeleteForm($user)->createView(),
            'files' =>$files,
            'transfersForm' => $transfersForm->createView(),
            'transfersFromTo' => $searchResults
        ]);
    }

    /**
     * Displays a form to edit user
     * 
     * @param Request $request
     * @param User $user
     * @Route("/{id}/edit", name="customer_edit", methods={"GET","POST"})
     */
    public function editAction(Request $request, User $user) 
    {
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm('App\Form\CustomerType', $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Customer edited');
            
            return $this->redirectToRoute('customer_show', array('id' => $user->getId()));
        }

        return $this->render('customer/edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ));
    }

    /**
     * Delete user entity
     * 
     * @param Request $request
     * @param User $user
     * @Route("/{id}", name="customer_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, User $user) 
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush($user);
            
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
     * Form to delete a user entity
     * 
     * @param User
     * @return Form
     */
    private function createDeleteForm(User $user) 
    {
        return $this->createFormBuilder()
          ->setAction($this->generateUrl('customer_delete', array('id' => $user->getId())))
          ->setMethod('DELETE')
          ->getForm();
    }

}