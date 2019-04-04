<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Dompdf\Dompdf;
use Dompdf\Options;

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
            return $this->redirectToRoute('user_files');
        } 
        
        return $this->redirectToRoute('dashboard');
    }
    
    /**
     * @Route("admin", name="dashboard")
     */
    public function dashboardAction() 
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $em = $this->getDoctrine()->getManager();
        $files = $em->getRepository('App:File')->filesByType();
  
        return $this->render('index/dashboard.html.twig', [
            'files' => $files
        ]);
    }

    /**
     * @Route("print", name="print")
     */    
    public function printData(Request $request)
    { 
        $output = '<link rel="stylesheet" 
                  href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" 
                  integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" 
                  crossorigin="anonymous">';
        $output .= $request->request->get('metadata');
        $output .= $request->request->get('content');
        $pdfOptions = new Options();
        
        $dompdf = new Dompdf($pdfOptions);        
        $dompdf->loadHtml($output);      
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("document.pdf", [
            "Attachment" => true
        ]);

    }
}