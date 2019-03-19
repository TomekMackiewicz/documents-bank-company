<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Table(name="transfers")
 * @ORM\Entity(repositoryClass="App\Repository\TransferRepository")
 */
class Transfer 
{
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
     *   message = "Type cannot be empty."
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
     * @ORM\ManyToMany(targetEntity="File", inversedBy="transfers")
     * @ORM\JoinTable(name="files_transfers")
     */
    private $files;
    
    /**
     * @var int
     * @ORM\Column(name="boxes", type="integer") 
     */
    private $boxes;

    public function __construct() 
    {
        $this->files = new ArrayCollection();
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
        $this->files[] = $file;
        return $this;
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
