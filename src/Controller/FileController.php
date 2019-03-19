<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Action;
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
 * @Route("file")
 */
class FileController extends Controller 
{
    /**
     * Lists files entities
     * 
     * @param Request $request
     * @Route("/", name="file_index")
     * @Method({"GET", "POST"})
     * @return array
     */
    public function indexAction(Request $request) 
    {
        $searchResults = [];
        $searchForm = $this->createSearchForm();
        $searchForm->handleRequest($request);
       
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $searchCriteria = $searchForm->getData();
            $em = $this->getDoctrine()->getManager();
            $searchResults = $em->getRepository('App:File')->searchFiles($searchCriteria);  
        }

        return $this->render('file/index.html.twig', array(
            'searchForm' => $searchForm->createView(),
            'searchResults' => $searchResults
        ));
    }

    /**
     * New file entity
     * 
     * @param Request $request
     * @Route("/new", name="file_new")
     * @Method({"GET", "POST"})
     * @return array
     */
    public function newAction(Request $request) 
    {
        $file = new File();
        $action = new Action();
        $form = $this->createForm('App\Form\FileType', $file);
        $form->handleRequest($request);
       
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();                
            $action->setFile($file);
            $action->setCustomer($file->getCustomer());
            $action->setDate(new \DateTime());
            $action->setAction($file->getStatus());

            $em->persist($file);
            $em->persist($action);
            $em->flush();
            
            $this->addFlash('success', 'New file created');
            
            return $this->redirectToRoute('file_index');
        }
        
        return $this->render('file/new.html.twig', array(
            'file' => $file,
            'form' => $form->createView()
        ));
    }

    /**
     * Show file
     * 
     * @param Request $request
     * @param File $file
     * @Route("/{id}", name="file_show")
     * @Method({"GET", "POST"})
     * @return array
     */
    public function showAction(Request $request, File $file) 
    {
        $actionsFromTo = null;
        $em = $this->getDoctrine()->getManager();
        $actionsForm = $this->createForm('App\Form\ActionType');
        $actionsForm->handleRequest($request);

        if ($actionsForm->isSubmitted() && $actionsForm->isValid()) {
            $dateFrom = $actionsForm["dateFrom"]->getData()->format('Y-m-d');
            $dateTo = $actionsForm["dateTo"]->getData()->format('Y-m-d');
            if( strtotime($dateFrom) < strtotime($dateTo) ) {
                $actionsFromTo = $em->getRepository('App:Action')
                    ->fileActionsFromTo($file->getId(), $dateFrom, $dateTo);
            } else { 
                $this->addFlash('error', "Start value can't be higher than end date");
            }
        }
        
        return $this->render('file/show.html.twig', [
            'file' => $file,
            'actionsForm' => $actionsForm->createView(),
            'actionsFromTo' => $actionsFromTo,            
            'delete_form' => $this->createDeleteForm($file)->createView()
        ]);
    }

    /**
     * Edit file
     * 
     * @Route("/{id}/edit", name="file_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, File $file) 
    {
        $action = new Action();
        $editForm = $this->createForm('App\Form\FileType', $file);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $action->setFile($file);
            $action->setCustomer($file->getCustomer());
            $action->setDate(new \DateTime());
            $action->setAction($file->getStatus());
            $em->persist($file);
            $em->persist($action);
            $em->flush();
            
            $this->addFlash('success', 'File edited successfully');

            return $this->redirectToRoute('file_show', array('id' => $file->getId()));
        }
        
        return $this->render('file/edit.html.twig', array(
            'file' => $file,
            'edit_form' => $editForm->createView(),
            'delete_form' => $this->createDeleteForm($file)->createView()
        ));
    }

    /**
     * Delete file
     * 
     * @param Request $request
     * @param File $file
     * @Route("/{id}", name="file_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, File $file) 
    {
        $form = $this->createDeleteForm($file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($file);
            $em->flush($file);
        }

        return $this->redirectToRoute('file_index');
    }

    /**
     * Creates a form to delete a file entity
     * 
     * @param File $file
     * @return Form
     */
    private function createDeleteForm(File $file) 
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('file_delete', array('id' => $file->getId())))
            ->setMethod('DELETE')
            ->getForm();
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
    
}
