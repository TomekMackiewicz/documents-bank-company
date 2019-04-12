<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\File;
use App\Entity\Transfer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class UserController extends AbstractController implements LogManagerInterface 
{
    /**
     * User files
     * 
     * @Route("profile/files", name="user_files", methods={"GET", "POST"})
     */
    public function filesAction(Request $request) 
    {
        $user = $this->getUser();
        $filesSearchResults = [];
        $filesSearchForm = $this->filesSearchForm();
        $filesSearchForm->handleRequest($request);
        $em = $this->getDoctrine()->getManager();
        $filesCount = $em->getRepository('App:Customer')->customerFilesByType($user->getCustomer()->getId());

        if ($filesSearchForm->isSubmitted() && $filesSearchForm->isValid()) {
            $filesSearchCriteria = $filesSearchForm->getData();
            $filesSearchCriteria['customer'] = $user->getCustomer();
            $filesSearchCriteria['sort'] = $filesSearchForm->get("sort")->getData();
            $filesSearchResults = $em->getRepository('App:File')->searchFiles($filesSearchCriteria);
        }       
        
        return $this->render('user/files.html.twig', array(
            'filesCount' => $filesCount,
            'filesSearchForm' => $filesSearchForm->createView(),
            'filesSearchResults' => $filesSearchResults           
        ));
    }

    /**
     * User transfers
     * 
     * @Route("profile/transfers", name="user_transfers", methods={"GET", "POST"})
     */
    public function transfersAction(Request $request, TranslatorInterface $translator) 
    {
        $user = $this->getUser();
        $transfersSearchResults = [];
        $transfersSearchForm = $this->transfersSearchForm();
        $transfersSearchForm->handleRequest($request);        
        
        if ($transfersSearchForm->isSubmitted() && $transfersSearchForm->isValid()) {
            $transfersSearchCriteria = $transfersSearchForm->getData(); 
            $transfersSearchCriteria['customer'] = $user->getCustomer();
            $transfersSearchCriteria['sort'] = $transfersSearchForm->get("sort")->getData();
            $dateFrom = $transfersSearchCriteria["dateFrom"]->format('Y-m-d');
            $dateTo = $transfersSearchCriteria["dateTo"]->format('Y-m-d');
            if(strtotime($dateFrom) <= strtotime($dateTo)) {
                $em = $this->getDoctrine()->getManager();
                $transfersSearchResults = $em->getRepository('App:Transfer')->searchTransfers($transfersSearchCriteria); 
            } else { 
                $this->addFlash('error', $translator->trans('start_date_higher_than_end_date'));
            }
        }        
        
        return $this->render('user/transfers.html.twig', array(
            'transfersSearchForm' => $transfersSearchForm->createView(),
            'transfersSearchResults' => $transfersSearchResults            
        ));
    }    

    /**
     * Show customer transfer
     * 
     * @param Transfer $transfer
     * @Route("profile/transfers/{id}", name="user_transfer", methods={"GET"})
     */
    public function transferAction(Transfer $transfer) 
    {        
        return $this->render('user/transfer.html.twig', [
            'transfer' => $transfer
        ]);
    }
    
    /**
     * Lists all user entities
     * 
     * @Route("admin/user/", name="user_index", methods={"GET"})
     */
    public function indexAction() 
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('App:User')->findAll();

        return $this->render('user/index.html.twig', array(
            'users' => $users
        ));
    }

    /**
     * Creates new user entity
     * 
     * @param Request $request
     * 
     * @Route("admin/user/new", name="user_new", methods={"GET","POST"})
     */
    public function newAction(Request $request, TranslatorInterface $translator) 
    {
        $user = new User();
        $user->setEnabled(true);
        $form = $this->createForm('App\Form\UserType', $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $password = $form['password']->getData();
            foreach ($form['roles']->getData() as $role) {
                $user->addRole($role);
            }            
            $user->setPlainPassword($password);
            
            $em->persist($user);
            $em->flush($user);
            
            $this->addFlash('success', $translator->trans('user_created'));

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
     * @param User $user
     * @Route("admin/user/{id}", name="user_show", methods={"GET","POST"})
     */
    public function showAction(User $user) 
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'delete_form' => $this->createDeleteForm($user)->createView(),
        ]);
    }

    /**
     * Displays a form to edit user
     * 
     * @param Request $request
     * @param User $user
     * @Route("admin/user/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function editAction(Request $request, User $user, TranslatorInterface $translator) 
    {
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm('App\Form\UserType', $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $translator->trans('user_edited'));
            
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
     * @Route("admin/user/{id}", name="user_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, User $user, TranslatorInterface $translator) 
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush($user);
            
            $this->addFlash('success', $translator->trans('user_deleted'));
        }
        
        return $this->redirectToRoute('user_index');
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

    /**
     * Create files search form
     * 
     * @return Form
     */
    private function filesSearchForm()
    {
        return $this->createFormBuilder(null)
            ->add('signature', SearchType::class, [
                'required' => false,
                'label' => false
            ])
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'file_status_in' => File::$statusIn,
                    'file_status_out' => File::$statusOut,
                    'file_status_unknown' => File::$statusUnknown,
                    'file_status_disposed' => File::$statusDisposed
                ],
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'label' => false
            ])
            ->add('sort', ChoiceType::class, [
                'choices'  => [
                    'signature_asc' => 'ASC',
                    'signature_desc' => 'DESC'
                ],
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'label' => false,
                'mapped' => false
            ])
            ->getForm();        
    }     

    /**
     * Create transfers search form
     * 
     * @return Form
     */
    private function transfersSearchForm()
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
                    'transfer_in' => Transfer::$transferIn,
                    'transfer_out' => Transfer::$transferOut,
                    'transfer_adjustment' => Transfer::$transferAdjustment
                ],
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'label' => false
            ])
            ->add('sort', ChoiceType::class, [
                'choices'  => [
                    'date_asc' => 'ASC',
                    'date_desc' => 'DESC'
                ],
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'label' => false,
                'mapped' => false
            ])
            ->getForm();        
    } 
    
}