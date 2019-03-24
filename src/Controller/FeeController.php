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
    public function calculateAction()
    {
        return $this->render('fee/calculate.html.twig', array(

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
        $feeTable = null;
        $datesError = null;
        $sum = null;
        $months = 0;
        $years = 0;
        $em = $this->getDoctrine()->getManager();
        $deleteForm = $this->createDeleteForm($fee); 
        $calculateFeeForm = $this->createForm('App\Form\FeeCountType');
        $calculateFeeForm->handleRequest($request);
        if ($calculateFeeForm->isSubmitted() && $calculateFeeForm->isValid()) {
            $dateFrom = $calculateFeeForm["dateFrom"]->getData()->format('Y-m-d');
            $dateTo = $calculateFeeForm["dateTo"]->getData()->format('Y-m-d');
            if( strtotime($dateFrom) < strtotime($dateTo) ) {
                $start = (new \DateTime($dateFrom))->modify('first day of this month');
                $end = (new \DateTime($dateTo))->modify('first day of next month');
                $interval = $end->diff($start);
                $interval->format('%m months');
                $months = $interval->m;
                $years = $interval->y;
                $feeTable = $em->getRepository('App:Fee')
                    ->actionsToCalculate($fee->getCustomer()->getId(), $dateFrom, $dateTo);
                $sum = ($feeTable['storage']*($interval->m + ($interval->y*12))) + 
                    ($feeTable['import']*$feeTable['actionIn']) +
                    ($feeTable['delivery']*$feeTable['actionOut']);
            } else {
                $datesError = "Start value can't be higher than end date!";              
            } 
        }

        return [
            'fee' => $fee,
            'delete_form' => $deleteForm->createView(),
            'calculateFeeForm' => $calculateFeeForm->createView(),
            'feeTable' => $feeTable,
            'sum' => $sum,
            'months' => $months,
            'years' => $years,
            'datesError' => $datesError
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