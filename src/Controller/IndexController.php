<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController 
{ 
    /**
     * @Route("/", name="index")
     */
    public function indexAction() 
    {
        if(!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('fos_user_security_login');
        }
        
        if(!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('fos_user_profile_show');
        } 
        
        return $this->redirectToRoute('dashboard');
    }
    
    /**
     * @Route("admin", name="dashboard")
     */
    public function dashboardAction() 
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
  
        $number=4;
        return $this->render('index/dashboard.html.twig', ['number' => $number]);
    }    
}