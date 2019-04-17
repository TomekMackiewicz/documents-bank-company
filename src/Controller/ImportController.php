<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Entity\File;
use App\Entity\Transfer;

/**
 * @Route("admin/import")
 */
class ImportController extends Controller 
{  
    /**
     * @Route("/", name="import_index")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request) 
    {
        set_time_limit(600);
        $form = $this->createForm('App\Form\ImportType');
        $form->handleRequest($request);
        $count = 0;

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['file']->getData();
            $customer = $form['customer']->getData();
            $spreadsheet = IOFactory::load($file);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            $total = count($sheetData);

            foreach ($sheetData as $line) {
                // Temporary fix
                $status = $line['C'] == 'Y' ? 1 : ($line['C'] == 'N' ? 2 : 3);

                // Temporary fix
                if ($line['B'] === null || $line['B'] === "BOX NUMBER" || $line['B'] === "Jan-18") {
                    continue;
                }

                $em = $this->getDoctrine()->getManager();

                $file = new File();
                $file->setCustomer($customer);
                $file->setSignature($line['B']);
                $file->setStatus($status);

                $transfer = new Transfer();                
                $transfer->addFile($file);
                $transfer->setCustomer($customer);
                $transfer->setDate(new \DateTime('now'));
                $transfer->setType(Transfer::$transferAdjustment);
                $transfer->setAdjustmentType(File::$statusIn);

                $em->persist($file);
                $em->persist($transfer);
                $em->flush();

                $count++;
            }

            $this->addFlash('success', "Zaimportowano ".$count." z ".$total." rekordów");
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', "Coś poszło nie tak, zaimportowano ".$count." rekordów");
        } 

        return $this->render('import/index.html.twig', array(
            'form' => $form->createView()
        ));
    }

}
