<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\DateType;

/**
 * @Route("admin/log")
 */
class LogController extends AbstractController
{
    /**
     * List log entries
     * 
     * @Route("/", name="log_index", methods={"GET", "POST"})
     */
    public function indexAction(Request $request) 
    {
        $searchResults = [];
        $form = $this->createSearchForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchCriteria = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $searchResults = $em->getRepository('App:Log')->searchLog($searchCriteria);  
        }

        return $this->render('log/index.html.twig', array(
            'form' => $form->createView(),
            'searchResults' => $searchResults
        ));
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
            ->getForm();        
    }     

}

