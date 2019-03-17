<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Action;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

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
    public function indexAction(Request $request) {
        return $this->render('file/index.html.twig');
    }

    /**
     * New file entity
     * 
     * @param Request $request
     * @Route("/new", name="file_new")
     * @Method({"GET", "POST"})
     * @return File
     */
    public function newAction(Request $request) {
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
        }
        
        return $this->render('file/new.html.twig', array(
            'file' => $file,
            'form' => $form->createView(),
        ));
    }
//
//    /**
//     * Finds and displays a box entity.
//     * @Route("/{id}", name="box_show")
//     * @Method({"GET", "POST"})
//     * @Template("box/show.html.twig")
//     */
//    public function showAction(Request $request, Box $box) {
//      $actionsFromTo = null;
//      $datesError = null;
//      $em = $this->getDoctrine()->getManager();
//      $repo = $this->getDoctrine()->getRepository('InventoryBundle:Action');
//      $actionsForm = $this->createForm('InventoryBundle\Form\ActionType');
//      $actionsForm->handleRequest($request);
//      if ($actionsForm->isSubmitted() && $actionsForm->isValid()) {
//        $dateFrom = $actionsForm["dateFrom"]->getData()->format('Y-m-d');
//        $dateTo = $actionsForm["dateTo"]->getData()->format('Y-m-d');
//        if( strtotime($dateFrom) < strtotime($dateTo) ) {
//          $actionsFromTo = $em->getRepository('InventoryBundle:Action')
//            ->boxActionsFromTo($box->getId(), $dateFrom, $dateTo);
//        } else {
//            $datesError = "Start value can't be higher than end date!";        
//        }
//      }
//      return [
//        'box' => $box,
//        'actionsForm' => $actionsForm->createView(),
//        'actionsFromTo' => $actionsFromTo,            
//        'delete_form' => $this->createDeleteForm($box)->createView(),
//        'datesError' => $datesError
//      ];
//    }
//
//    /**
//     * Displays a form to edit an existing box entity.
//     * @Route("/{id}/edit", name="box_edit")
//     * @Method({"GET", "POST"})
//     */
//    public function editAction(Request $request, Box $box) {
//      $action = new Action();
//      $editForm = $this->createForm('InventoryBundle\Form\BoxType', $box);
//      $editForm->handleRequest($request);
//
//      if ($editForm->isSubmitted() && $editForm->isValid()) {
//        $em = $this->getDoctrine()->getManager();
//        $action->setBox($box);
//        $action->setCustomer($box->getCustomer());
//        $action->setDate($editForm['lastAction']->getData());
//        $action->setAction($box->getStatus());
//        $em->persist($box);
//        $em->persist($action);
//        $em->flush();
//
//        return $this->redirectToRoute('box_show', array('id' => $box->getId()));
//      }
//      return $this->render('box/edit.html.twig', array(
//        'box' => $box,
//        'edit_form' => $editForm->createView(),
//        'delete_form' => $this->createDeleteForm($box)->createView()
//      ));
//    }
//
//    /**
//     * Deletes a box entity.
//     * @Route("/{id}", name="box_delete")
//     * @Method("DELETE")
//     */
//    public function deleteAction(Request $request, Box $box) {
//      $form = $this->createDeleteForm($box);
//      $form->handleRequest($request);
//
//      if ($form->isSubmitted() && $form->isValid()) {
//        $em = $this->getDoctrine()->getManager();
//        $em->remove($box);
//        $em->flush($box);
//      }
//      return $this->redirectToRoute('box_index');
//    }
//
//    /**
//     * Creates a form to delete a box entity.
//     * @param Box $box The box entity
//     * @return \Symfony\Component\Form\Form The form
//     */
//    private function createDeleteForm(Box $box) {
//      return $this->createFormBuilder()
//        ->setAction($this->generateUrl('box_delete', array('id' => $box->getId())))
//        ->setMethod('DELETE')
//        ->getForm();
//    }

}
