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
     * @param  File|null $file
     * @return string
     */
    public function transform($file)
    {
        if (null === $file) {
            return '';
        }

        return $file;
    }

    /**
     * Transforms a string to an object
     *
     * @param  string $signature
     * @return File|null
     * @throws TransformationFailedException if object is not found
     */
    public function reverseTransform($signature)
    {
        if (!$signature) {
            return;
        }

        
        $signatures = explode(',', $signature);
       
        foreach ($signatures as $signature) {
            if (empty($signature)) {
                continue;
            }
            $files[] = $this->entityManager->getRepository(File::class)->findOneBy(array('signature' => trim($signature)));
        }

        if (null === $files) {
            throw new TransformationFailedException(sprintf(
                'File with signature "%s" does not exist!',
                $signature
            ));
        }

        return $files;
    }
}