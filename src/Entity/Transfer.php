<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Entity\File;

/**
 * @ORM\Table(name="transfers")
 * @ORM\Entity(repositoryClass="App\Repository\TransferRepository")
 */
class Transfer 
{
    static $transferIn = 1;
    static $transferOut = 2;
    static $transferAdjustment = 3;
    
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="type", type="integer") 
     * @Assert\NotBlank(
     *   message = "field_cannot_be_empty"
     * ) 
     */
    private $type;

    /**
     * @var \Date
     * @ORM\Column(name="date", type="date")    
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="transfers")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $customer;

    /**
     * @ORM\ManyToMany(targetEntity="File", inversedBy="transfers", cascade={"persist"}, indexBy="signature", fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="files_transfers")
     */
    private $files;
    
    /**
     * @var int
     * @Assert\Regex(
     *     pattern = "/^[0-9]*$/",
     *     match = true,
     *     message = "only_digits"
     * ) 
     * @ORM\Column(name="boxes", type="integer", nullable=true) 
     */
    private $boxes;

    public function __construct() 
    {
        $this->files = new ArrayCollection();
    }    

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        $transferDate = $this->getDate()->format('Y-m-d');
        $files = $this->getFiles();
        $signatures = [];
        $duplicates = [];
        $nonExistent = [];
        $invalidType = [];
        $disposed = [];
        $unknown = [];

        foreach ($files as $file) {
            $signature = $file->getSignature();
            $signatures[] = $signature;
            
            if (strpos($signature, 'd_') !== false || strpos($signature, 'n_') !== false) {
                continue;
            } 
            
            $previousTransfer = $file->getLastTransactionForDate($transferDate);
            $nextTransfer = $file->getNextTransactionForDate($transferDate);

            // If previous transfer and previous transfer type equals this transfer type
            if (false !== $previousTransfer && $previousTransfer->getType() == $this->getType()) {
               $invalidType[] = $signature; 
            }
            
            // If next transfer and next transfer type not equals this transfer type
            if (false !== $nextTransfer && $nextTransfer->getType() == $this->getType()) {
               $invalidType[] = $signature; 
            }
            
//            If file is in the stock while transfer request is from customer & vice versa
//            if ($file->getStatus() == $this->getType()) {
//               $invalidType[] = $signature; 
//            }

            // If file is disposed
            if ($file->getStatus() == File::$statusDisposed) {
               $disposed[] = $signature; 
            }
            // If file is missing
            if ($file->getStatus() == File::$statusUnknown) {
               $unknown[] = $signature; 
            }             
        }

        foreach ($signatures as $signature) {
            // If file is duplicated
            if (strpos($signature, 'd_') !== false) {
                $duplicates[] = substr($signature, strpos($signature, "_") + 1);
            }
            // If file with this signature does not exists
            if (strpos($signature, 'n_') !== false) {
                $nonExistent[] = substr($signature, strpos($signature, "_") + 1);
            }            
        }
       
        if (!empty($duplicates)) {
            $context->buildViolation('File(s) '.implode(', ', $duplicates).' are duplicated.')
                    ->atPath('files')
                    ->addViolation();             
        }
        if (!empty($nonExistent)) {
            $context->buildViolation('File(s) '.implode(', ', $nonExistent).' does not exists.')
                    ->atPath('files')
                    ->addViolation(); 
        } 
        if (!empty($invalidType)) {
            $context->buildViolation('File(s) '.implode(', ', $invalidType).' has invalid status.') //$this->getType()
                    ->atPath('files')
                    ->addViolation(); 
        } 
        if (!empty($disposed)) {
            $context->buildViolation('File(s) '.implode(', ', $disposed).' are disposed.')
                    ->atPath('files')
                    ->addViolation(); 
        } 
        if (!empty($unknown)) {
            $context->buildViolation('File(s) '.implode(', ', $unknown).' are missing (status unknown).')
                    ->atPath('files')
                    ->addViolation(); 
        }       
    }

    /**
     * @return integer 
     */
    public function getId() 
    {
        return $this->id;
    }

    /**
     * @param \Date $date
     * @return Transfer
     */
    public function setDate($date) 
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return \Date 
     */
    public function getDate() 
    {
        return $this->date;
    }

    /**
     * @param string $type
     * @return Transfer
     */
    public function setType($type) 
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string 
     */
    public function getType() 
    {
        return $this->type;
    }

    /**
     * @param Customer $customer
     * @return Action
     */
    public function setCustomer(Customer $customer = null) 
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return Customer 
     */
    public function getCustomer() 
    {
        return $this->customer;
    }

    /**
     * @param File $file
     * @return Transfer
     */
    public function addFile(File $file)
    {
        if ($this->files->contains($file)) {
            return $this;
        }

        $tagKey = $file->getSignature() ?? $file->getId();
        $this->files[$tagKey] = $file;        
    }
    
    /**
     * Remove file
     *
     * @param File $file
     */
    public function removeFile(File $file)
    {
        $this->files->removeElement($file);
    }
    
    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }
    
    /**
     * @param int $boxes
     * @return Transfer
     */
    public function setBoxes($boxes) 
    {
        $this->boxes = $boxes;
        return $this;
    }

    /**
     * @return int
     */
    public function getBoxes() 
    {
        return $this->boxes;
    }    
}
