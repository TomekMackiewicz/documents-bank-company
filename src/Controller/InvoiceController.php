<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("invoice")
 */
class InvoiceController extends Controller 
{
    /**
     * Print invoice
     * 
     * @Route("/print", name="invoice_print")
     * @param string
     * @Method({"GET", "POST"})
     */
    public function printAction()
    {
        $folder = 'invoices/';
        $date = time();
        
//        $phpWord = new \PhpOffice\PhpWord\PhpWord();
//
//        $section = $phpWord->addSection();
//        // Adding Text element to the Section having font styled by default...
//        $section->addText(
//            '"Learn from yesterday, live for today, hope for tomorrow. '
//                . 'The important thing is not to stop questioning." '
//                . '(Albert Einstein)'
//        );
//        $section = $phpWord->addSection();
//        // Adding Text element to the Section having font styled by default...
//        $section->addText(
//            '"Learn from yesterday, live for today, hope for tomorrow. '
//                . 'The important thing is not to stop questioning." '
//                . '(Albert Einstein)'
//        );
//        
//        // Saving the document as OOXML file...
//        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
//        $objWriter->save($folder.'helloWorld.docx');        
        
        return $this->render('invoice/print.html.twig', array(
            'data' => time()
        ));
    }
    
}
