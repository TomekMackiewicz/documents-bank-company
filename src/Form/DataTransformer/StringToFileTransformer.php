<?php

namespace App\Form\DataTransformer;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class StringToFileTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object to a string
     *
     * @param  ArrayCollection|null $collection
     * @return string
     */
    public function transform($collection)
    {
        if (!$collection) {
            return;
        }
        
        $files = $collection->toArray();
        $signatures = [];
        
        foreach ($files as $file) {
            $signatures[] = $file->getSignature();
        }

        return implode(', ', $signatures);
    }

    /**
     * Transforms a string to an object
     *
     * @param  string $signaturesString
     * @return array|null
     * @throws TransformationFailedException if object is not found
     */
    public function reverseTransform($signaturesString)
    {
        if (!$signaturesString) {
            return;
        }
       
        $signatures = explode(',', $signaturesString);

        $alreadyProcessed = [];
        foreach ($signatures as $signature) {
            $trimmed = trim($signature);
            if (empty($trimmed)) {
                continue;
            }
            $file = $this->entityManager->getRepository(File::class)->findOneBy(array('signature' => $trimmed));

            if (!$file) {
                $dummy = new File();
                $dummy->setSignature('n_'.$trimmed);                
                $files[] = $dummy;
            } elseif(in_array($trimmed, $alreadyProcessed)) {
                $dummy = new File();
                $dummy->setSignature('d_'.$trimmed);                
                $files[] = $dummy;                
            } else {
                $files[] = $file;
            }

            $alreadyProcessed[] = $trimmed;                       
        }

        if (null === $files) {
            throw new TransformationFailedException(sprintf(
                'Files cannot be null',
                $signaturesString
            ));
        }

        return $files;
    }
}