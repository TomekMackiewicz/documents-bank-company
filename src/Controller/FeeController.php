<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Fee;

/**
 * @Route("fee")
 */
class FeeController extends Controller 
{
    
    /**
     * Calculate fee
     * 
     * @Route("/calculate", name="fee_calculate")
     * @Method({"GET", "POST"})
     */    
    public function calculateAction(Request $request)
    {        
        $calculation = null;
        $calculateFeeForm = $this->createForm('App\Form\FeeCountType');
        $calculateFeeForm->handleRequest($request);

        if ($calculateFeeForm->isSubmitted() && $calculateFeeForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $calculateFeeForm->getData();
            
            $dateFrom = $data["dateFrom"]->format('Y-m-d');
            $dateTo = $data["dateTo"]->format('Y-m-d');
            $customer = $data['customer']->getId();
            $calculation = $em->getRepository('App:Fee')->actionsToCalculate($customer, $dateFrom, $dateTo);
        }
        
        return $this->render('fee/calculate.html.twig', array(
            'calculation' => $calculation,
            'form' => $calculateFeeForm->createView()
        ));        
    }    

    /**
     * Lists all fees
     * 
     * @Route("/", name="fee_index")
     * @Method({"GET", "POST"})
     * @Template("fee/index.html.twig")
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
     * @Route("/new", name="fee_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request) 
    {
        $fee = new Fee();
        $form = $this->createForm('App\Form\FeeType', $fee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($fee);
            $em->flush($fee);

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
     * @Route("/{id}", name="fee_show")
     * @Method({"GET", "POST"})
     * @Template("fee/show.html.twig")
     */
    public function showAction(Request $request, Fee $fee) 
    {
        $deleteForm = $this->createDeleteForm($fee); 

        return [
            'fee' => $fee,
            'delete_form' => $deleteForm->createView()
        ];
    }

    /**
     * Edit fee
     * 
     * @Route("/{id}/edit", name="fee_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Fee $fee) 
    {
        $deleteForm = $this->createDeleteForm($fee);
        $editForm = $this->createForm('App\Form\FeeType', $fee);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
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
     * @Route("/{id}", name="fee_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Fee $fee) 
    {
        $form = $this->createDeleteForm($fee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($fee);
            $em->flush($fee);
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