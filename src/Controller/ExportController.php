<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\File;

/**
 * @Route("admin/export")
 */
class ExportController extends Controller 
{ 
    /**
     * @Route("/", name="export_index")
     * @Method({"GET", "POST"})
     */
    public function indexAction(Request $request, TranslatorInterface $translator) 
    {
        set_time_limit(600);
        $form = $this->createForm('App\Form\ExportType');
        $form->handleRequest($request);
$files = [];        
        if ($form->isSubmitted() && $form->isValid()) {
            $searchCriteria = [];
            $searchCriteria['customer'] = $form['customer']->getData();
            $searchCriteria['sort'] = 'ASC';
            $filename = $form['filename']->getData();
            
            $files = $this->getDoctrine()->getManager()->getRepository('App:File')->searchFiles($searchCriteria);
            
            $spreadsheet = new Spreadsheet();
            $writer = IOFactory::createWriter($spreadsheet, "Xlsx");	
            $spreadsheet->setActiveSheetIndex(0);
            $sheet = $spreadsheet->getActiveSheet();
            
            $sheet->setCellValue('A1', $translator->trans('signature'));
            $sheet->setCellValue('B1', $translator->trans('status'));
            $sheet->setCellValue('C1', $translator->trans('note'));
            $sheet->setCellValue('D1', $translator->trans('location'));
            
            $i = 3;
            foreach ($files as $file) {
                $sheet->setCellValue('A'.$i, $file->getSignature());
                $sheet->setCellValue('B'.$i, $this->translateStatus($file->getStatus(), $translator));
                $sheet->setCellValue('C'.$i, $file->getNote());
                $sheet->setCellValue('D'.$i, $file->getLocation());
                
                $i++;
            }

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $writer->save('php://output');
            exit();
        }
        
        return $this->render('export/index.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    private function translateStatus($status, TranslatorInterface $translator)
    {
        switch ($status) {
            case File::$statusIn:
                return $translator->trans('file_status_in');
            case File::$statusOut:
                return $translator->trans('file_status_out');
            case File::$statusUnknown:
                return $translator->trans('file_status_unknown');
            case File::$statusDisposed:
                return $translator->trans('file_status_disposed');
        }
    }

}
