<?php

namespace App\Controller;

use App\Entity\Transfer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @Route("admin/transfer")
 */
class TransferController extends Controller 
{
    /**
     * List of transfers
     * 
     * @param Request $request
     * @Route("/", name="transfer_index", methods={"GET","POST"})
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
            $searchResults = $em->getRepository('App:Transfer')->searchTransfers($searchCriteria);  
        }

        return $this->render('transfer/index.html.twig', array(
            'searchForm' => $searchForm->createView(),
            'searchResults' => $searchResults
        ));
    } 

    /**
     * New transfer
     * 
     * @param Request $request
     * @Route("/new", name="transfer_new", methods={"GET","POST"})
     * @return array
     */
    public function newAction(Request $request) 
    {
        $transfer = new Transfer();
        $form = $this->createForm('App\Form\TransferType', $transfer);
        $form->handleRequest($request);
     
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager(); 
            
            foreach ($data->getFiles()->toArray() as $file) {
                $file->setStatus($data->getType());
                $transfer->addFile($file);
                $em->persist($file);               
            }
            
            $transfer->setCustomer($data->getCustomer());
            $transfer->setDate($data->getDate());
            $transfer->setType($data->getType());

            $em->persist($transfer);
            $em->flush();
            
            $this->addFlash('success', 'New transfer created');
            
            return $this->redirectToRoute('transfer_index');
        }
        
        return $this->render('transfer/new.html.twig', array(
            'transfer' => $transfer,
            'form' => $form->createView()
        ));
    }

    /**
     * Show transfer
     * 
     * @param Transfer $transfer
     * @Route("/{id}", name="transfer_show", methods={"GET","POST"})
     */
    public function showAction(Transfer $transfer) 
    {        
        return $this->render('transfer/show.html.twig', [
            'transfer' => $transfer,            
            'delete_form' => $this->createDeleteForm($transfer)->createView()
        ]);
    }    

    /**
     * Edit transfer
     * 
     * @param Request $request
     * @param Transfer $transfer
     * @Route("/{id}/edit", name="transfer_edit", methods={"GET","POST"})
     */
    public function editAction(Request $request, Transfer $transfer) 
    {
        $editForm = $this->createForm('App\Form\TransferType', $transfer);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $data = $editForm->getData();
            $em = $this->getDoctrine()->getManager(); 
            
            foreach ($data->getFiles()->toArray() as $file) {
                $file->setStatus($data->getType());
                $transfer->addFile($file);
                $em->persist($file);               
            }
            
            $transfer->setCustomer($data->getCustomer());
            $transfer->setDate($data->getDate());
            $transfer->setType($data->getType());

            $em->persist($transfer);
            $em->flush();
            
            $this->addFlash('success', 'Transfer edited');
            
            return $this->redirectToRoute('transfer_index');
        }
        
        return $this->render('transfer/edit.html.twig', array(
            'transfer' => $transfer,
            'edit_form' => $editForm->createView(),
            'delete_form' => $this->createDeleteForm($transfer)->createView()
        ));
    }    
    
    /**
     * Delete transfer
     * 
     * @param Request $request
     * @param Transfer $transfer
     * @Route("/{id}", name="transfer_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Transfer $transfer) 
    {
        $form = $this->createDeleteForm($transfer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($transfer);
            $em->flush($transfer);
            
            $this->addFlash('success', 'Transfer deleted');
        }

        return $this->redirectToRoute('transfer_index');
    }    
    
    /**
     * Creates a form to delete a transfer
     * 
     * @param Transfer $transfer
     * @return Form
     */
    private function createDeleteForm(Transfer $transfer) 
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('transfer_delete', array('id' => $transfer->getId())))
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
            ->add('customer', EntityType::class, [
                'class' => 'App:Customer',
                'choice_label' => 'company',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.company', 'ASC')
                        ->where('c.roles NOT LIKE :roles')
                        ->setParameter('roles', '%ADMIN%');
                },
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'label' => false
            ])
            ->getForm();        
    }    
    
}
