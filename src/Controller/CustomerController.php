<?php

namespace App\Controller;

use App\Entity\User;
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
     * Lists all user entities
     * 
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function indexAction() 
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('App:User')->excludeAdmin();

        return $this->render('user/index.html.twig', array(
            'users' => $users
        ));
    }

    /**
     * Creates new user entity
     * 
     * @param Request $request
     * 
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function newAction(Request $request) 
    {
        $user = new User();
        $user->setEnabled(true);
        $form = $this->createForm('App\Form\UserType', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $password = $form['password']->getData();
            $user->setPlainPassword($password);
            
            $em->persist($user);
            $em->flush($user);
            
            $this->addFlash('success', 'User created');

            return $this->redirectToRoute('user_show', array('id' => $user->getId()));
        }
        
        return $this->render('user/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays user
     * 
     * @param Request $request
     * @Route("/{id}", name="user_show", methods={"GET","POST"})
     */
    public function showAction(Request $request, User $user) 
    {
        $searchResults = [];

        $em = $this->getDoctrine()->getManager();
        $filesIn  = $em->getRepository('App:User')->filesInCountByUser($user->getId());
        $filesOut = $em->getRepository('App:User')->filesOutCountByUser($user->getId());
        $transfersForm = $this->createSearchForm();
        $transfersForm->handleRequest($request);

        if ($transfersForm->isSubmitted() && $transfersForm->isValid()) {
            $searchCriteria = $transfersForm->getData();
            $searchCriteria['user'] = $user;
            $searchResults = $em->getRepository('App:Transfer')->searchTransfers($searchCriteria); 
        }

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'delete_form' => $this->createDeleteForm($user)->createView(),
            'filesIn' =>$filesIn,
            'filesOut' =>$filesOut,
            'transfersForm' => $transfersForm->createView(),
            'transfersFromTo' => $searchResults
        ]);
    }

    /**
     * Displays a form to edit user
     * 
     * @param Request $request
     * @param User $user
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function editAction(Request $request, User $user) 
    {
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm('App\Form\UserType', $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'User edited');
            
            return $this->redirectToRoute('user_show', array('id' => $user->getId()));
        }

        return $this->render('user/edit.html.twig', array(
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
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, User $user) 
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush($user);
            
            $this->addFlash('success', 'User deleted');
        }
        
        return $this->redirectToRoute('user_index');
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
          ->setAction($this->generateUrl('user_delete', array('id' => $user->getId())))
          ->setMethod('DELETE')
          ->getForm();
    }

}