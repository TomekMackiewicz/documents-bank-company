<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\Transfer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Doctrine\ORM\EntityRepository;

/**
 * @Route("admin/file")
 */
class FileController extends AbstractController implements LogManagerInterface 
{
    /**
     * API to autocomplete
     * 
     * @Route("/api/{text}/{customer}/{type}", name="files_api", methods={"GET"})
     * @param string $text
     * @param string $customer
     * @param string $type
     * 
     * @return JSON
     */
    public function filesAction($text, $customer, $type)
    {
        $data = $this->getDoctrine()->getManager()->getRepository('App:File')->getAllFiles($text, $customer, $type);
        $files = array_column($data, 'signature');

        return $this->json($files);
    }
    
    /**
     * Lists files entities
     * 
     * @param Request $request
     * @Route("/", name="file_index", methods={"GET","POST"})
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
            $searchResults = $em->getRepository('App:File')->searchFiles($searchCriteria);  
        }

        return $this->render('file/index.html.twig', array(
            'searchForm' => $searchForm->createView(),
            'searchResults' => $searchResults
        ));
    }

    /**
     * New file entity
     * 
     * @param Request $request
     * @Route("/new", name="file_new", methods={"GET","POST"})
     * @return array
     */
    public function newAction(Request $request) 
    {
        $file = new File();
        $transfer = new Transfer();
        $form = $this->createForm('App\Form\FileType', $file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            
            $signatures = explode(',', $data->getSignature());
            $transfer->setDate(new \DateTime());
            $transfer->setType(Transfer::$transferAdjustment);
            $transfer->setCustomer($data->getCustomer());
            
            $filesAlreadyIn = [];
            foreach ($signatures as $signature) {
                if (empty($signature)) {
                    continue;
                }
                
                $signature = trim(strtoupper($signature));
                
                $fileToCheck = $em->getRepository('App:File')->checkFileAlreadyExists($signature, $data->getCustomer());
                if ($fileToCheck) {
                    $filesAlreadyIn[] = $signature;
                    continue;
                }
                
                $file = new File();
                $file->setSignature($signature);
                $file->setStatus($data->getStatus());
                $file->setNote($data->getNote());
                $file->setCustomer($data->getCustomer());
                $file->addTransfer($transfer);
                $em->persist($file);
                
                $transfer->addFile($file);
            }
            
            if (!empty($filesAlreadyIn)) {
                $this->addFlash('error', 'File(s) '.implode(',', $filesAlreadyIn).' for customer '.$data->getCustomer()->getName().' already exists');
                return $this->render('file/new.html.twig', array(
                    'file' => $file,
                    'form' => $form->createView()
                ));
            }
            
            $em->persist($transfer);
            $em->flush();
            
            $this->addFlash('success', 'New file(s) created');
            
            return $this->redirectToRoute('file_index');
        }
        
        return $this->render('file/new.html.twig', array(
            'file' => $file,
            'form' => $form->createView()
        ));
    }

    /**
     * Show file
     * 
     * @param Request $request
     * @param File $file
     * @Route("/{id}", name="file_show", methods={"GET","POST"})
     * @return array
     */
    public function showAction(Request $request, File $file) 
    {
        $transfersFromTo = null;
        $em = $this->getDoctrine()->getManager();
        $transfersForm = $this->createForm('App\Form\ActionType');
        $transfersForm->handleRequest($request);

        if ($transfersForm->isSubmitted() && $transfersForm->isValid()) {
            $dateFrom = $transfersForm["dateFrom"]->getData()->format('Y-m-d');
            $dateTo = $transfersForm["dateTo"]->getData()->format('Y-m-d');
            if(strtotime($dateFrom) <= strtotime($dateTo)) {
                $transfersFromTo = $em->getRepository('App:Transfer')
                    ->fileTransfersFromTo($file->getId(), $dateFrom, $dateTo);
            } else { 
                $this->addFlash('error', "Start value cannot be higher than end date");
            }
        }
        
        return $this->render('file/show.html.twig', [
            'file' => $file,
            'transfersForm' => $transfersForm->createView(),
            'transfersFromTo' => $transfersFromTo,            
            'delete_form' => $this->createDeleteForm($file)->createView()
        ]);
    }

    /**
     * Edit file
     * 
     * @param Request $request
     * @param File $file
     * @Route("/{id}/edit", name="file_edit", methods={"GET","POST"})
     * @return array
     */
    public function editAction(Request $request, File $file) 
    {
        $fileStatus = $file->getStatus();
        $editForm = $this->createForm('App\Form\FileType', $file);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            
            // Add new transfer only if file status changed
            if ($fileStatus !== $editForm['status']->getData()) {
                $transfer = new Transfer();
                $transfer->addFile($file);
                $transfer->setCustomer($file->getCustomer());
                $transfer->setDate(new \DateTime());
                $transfer->setType(Transfer::$transferAdjustment); 
                $em->persist($transfer);
            }            
            
            $em->flush();
            
            $this->addFlash('success', 'File edited successfully');

            return $this->redirectToRoute('file_show', array('id' => $file->getId()));
        }
        
        return $this->render('file/edit.html.twig', array(
            'file' => $file,
            'edit_form' => $editForm->createView(),
            'delete_form' => $this->createDeleteForm($file)->createView()
        ));
    }

    /**
     * Delete file
     * 
     * @param Request $request
     * @param File $file
     * @Route("/{id}", name="file_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, File $file) 
    {
        $form = $this->createDeleteForm($file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $orphanedTransfers = $em->getRepository('App:File')->getOrphanedTransfers($file);
            
            $em->remove($file);
            $em->flush($file);

            foreach ($orphanedTransfers as $orphan) {
                $em->remove($orphan);
                $em->flush($orphan);
            }

            $this->addFlash('success', 'File deleted');
        }
        
        return $this->redirectToRoute('file_index');
    }

    /**
     * Creates a form to delete a file entity
     * 
     * @param File $file
     * @return Form
     */
    private function createDeleteForm(File $file) 
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('file_delete', array('id' => $file->getId())))
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
            ->add('signature', SearchType::class, [
                'required' => false,
                'label' => false
            ])
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'file_status_in' => File::$statusIn,
                    'file_status_out' => File::$statusOut,
                    'file_status_unknown' => File::$statusUnknown,
                    'file_status_disposed' => File::$statusDisposed
                ],
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'label' => false
            ])
            ->add('customer', EntityType::class, [
                'class' => 'App:Customer',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'required' => false,
                'expanded' => false,
                'multiple' => true,
                'label' => false
            ])
            ->getForm();        
    } 
    
}
