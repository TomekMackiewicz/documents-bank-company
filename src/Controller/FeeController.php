<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Fee;

/**
 * @Route("admin/fee")
 */
class FeeController extends AbstractController 
{    
    /**
     * Calculate fee
     * 
     * @param Request $request
     * @Route("/calculate", name="fee_calculate", methods={"GET","POST"})
     */    
    public function calculateAction(Request $request, TranslatorInterface $translator)
    { 
        $calculation = null;
        $calculateFeeForm = $this->createForm('App\Form\FeeCountType');
        $calculateFeeForm->handleRequest($request);

        if ($calculateFeeForm->isSubmitted() && $calculateFeeForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $calculateFeeForm->getData();
            
            $month = $data["month"]->format('Y-m');
            $customer = $data['customer']->getId();
            $calculation = $em->getRepository('App:Fee')->actionsToCalculate($customer, $month);
            
            if (!$calculation) {
                $this->addFlash('error', $translator->trans('add_fees_first'));
            }            
        }
        
        return $this->render('fee/calculate.html.twig', array(
            'calculation' => $calculation,
            'form' => $calculateFeeForm->createView()
        ));        
    }    

    /**
     * Lists all fees
     * 
     * @Route("/", name="fee_index", methods={"GET"})
     */
    public function indexAction() 
    {
        $em = $this->getDoctrine()->getManager();
        $fees = $em->getRepository('App:Fee')->findAll();

        return $this->render('fee/index.html.twig', array(
            'fees' => $fees
        ));
    }

    /**
     * Create new fee
     * 
     * @param Request $request
     * @Route("/new", name="fee_new", methods={"GET","POST"})
     */
    public function newAction(Request $request, TranslatorInterface $translator) 
    {
        $fee = new Fee();
        $form = $this->createForm('App\Form\FeeType', $fee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($fee);
            $em->flush($fee);
            
            $this->addFlash('success', $translator->trans('fee_added'));

            return $this->redirectToRoute('fee_show', array('id' => $fee->getId()));
        }
        return $this->render('fee/new.html.twig', array(
            'fee' => $fee,
            'form' => $form->createView(),
        ));
    }

    /**
     * Show fee entity
     * 
     * @param Fee $fee
     * @Route("/{id}", name="fee_show", methods={"GET","POST"})
     */
    public function showAction(Fee $fee) 
    {
        $deleteForm = $this->createDeleteForm($fee); 

        return $this->render('fee/show.html.twig', [
            'fee' => $fee,
            'delete_form' => $deleteForm->createView()
        ]);
    }

    /**
     * Edit fee
     * 
     * @param Request $request
     * @param Fee $fee
     * @Route("/{id}/edit", name="fee_edit", methods={"GET","POST"})
     */
    public function editAction(Request $request, Fee $fee, TranslatorInterface $translator) 
    {
        $deleteForm = $this->createDeleteForm($fee);
        $editForm = $this->createForm('App\Form\FeeType', $fee);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            
            $this->addFlash('success', $translator->trans('fee_edited'));
            
            return $this->redirectToRoute('fee_show', array('id' => $fee->getId()));
        }
        
        return $this->render('fee/edit.html.twig', array(
            'fee' => $fee,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ));
    }

    /**
     * Deletes fee
     * 
     * @param Request $request
     * @param Fee $fee
     * @Route("/{id}", name="fee_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Fee $fee, TranslatorInterface $translator) 
    {
        $form = $this->createDeleteForm($fee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($fee);
            $em->flush($fee);
            
            $this->addFlash('success', $translator->trans('fee_deleted'));
        }
        
        return $this->redirectToRoute('fee_index');
    }

    /**
     * Creates a form to delete a fee entity
     * 
     * @param Fee $fee The fee entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Fee $fee)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('fee_delete', array('id' => $fee->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}