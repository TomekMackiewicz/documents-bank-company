<?php

namespace App\Controller;

use App\Entity\Transfer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
//use Symfony\Component\Form\Extension\Core\Type\SearchType;
//use Symfony\Component\Form\Extension\Core\Type\SubmitType;
//use Symfony\Bridge\Doctrine\Form\Type\EntityType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Doctrine\ORM\EntityRepository;

/**
 * @Route("transfer")
 */
class TransferController extends Controller 
{
    /**
     * List of transfers
     * 
     * @param Request $request
     * @Route("/", name="transfer_index")
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
            $searchResults = $em->getRepository('App:Transfer')->searchTransfers($searchCriteria);  
        }

        return $this->render('transfer/index.html.twig', array(
            'searchForm' => $searchForm->createView(),
            'searchResults' => $searchResults
        ));
    } 
    
//////////////////////////

    /**
     * New transfer
     * 
     * @param Request $request
     * @Route("/new", name="transfer_new")
     * @Method({"GET", "POST"})
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

            $transfer->addFiles($data['files']);
            $transfer->setCustomer($data['customer']);
            $transfer->setDate(new \DateTime());
            $transfer->setType($data['type']);

            // Trzeba zmienić status pudeł!!!
            //$em->persist($file);
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
     * @param Request $request
     * @param Transfer $transfer
     * @Route("/{id}", name="transfer_show")
     * @Method({"GET", "POST"})
     */
    public function showAction(Request $request, Transfer $transfer) 
    {
        
        return $this->render('transfer/show.html.twig', [
            'transfer' => $transfer,            
            'delete_form' => $this->createDeleteForm($transfer)->createView()
        ]);
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
                'label' => 'From',
                'widget' => 'single_text'                
            ))
            ->add('dateTo', DateType::class, array(
                'label' => 'To',
                'widget' => 'single_text'                
            ))                
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
