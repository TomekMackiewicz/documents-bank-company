<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController 
{
  
//    /**
//     * @Route("/", name="index")
//     */
    public function indexAction() 
    {
        $number=4;
        return $this->render('index/index.html.twig', ['number' => $number]);
    }
}