<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="files")
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 * @UniqueEntity("signature")
 */
class File 
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
     * @ORM\Column(name="signature", type="string", length=32)
     * @Assert\NotBlank(
     *   message = "Signature cannot be empty."
     * ) 
     * @Assert\Regex(
     *     pattern = "/^[a-zA-Z0-9]*$/",
     *     match = true,
     *     message = "Only letters and digits are allowed"
     * )  
     */
    private $signature;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=8)
     * @Assert\NotBlank(
     *   message = "Status cannot be empty."
     * )
     * @Assert\Choice(
     *   choices = { "In", "Out", "Unknown" },
     *   message = "Choose a valid value (In, Out, Unknown)."
     * )        
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(name="note", type="text", nullable=true)       
     */
    private $note;    
    
    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="files")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotBlank(
     *   message = "Customer field cannot be empty."
     * )    
     */
    private $customer;

    /**
     * @ORM\OneToMany(targetEntity="Action", mappedBy="file")
     */
    private $actions;

    public function __construct() 
    {
        $this->actions = new ArrayCollection();
    }

    /**
     * @return integer 
     */
    public function getId() 
    {
        return $this->id;
    }

    /**
     * @param string $signature
     * @return File
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
        return $this;
    }

    /**
     * @return string 
     */
    public function getSignature() 
    {
        return $this->signature;
    }

    /**
     * @param string $status
     * @return File
     */
    public function setStatus($status) 
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string 
     */
    public function getStatus() 
    {
        return $this->status;
    }

    /**
     * @param string $note
     * @return File
     */
    public function setNote($note) 
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return string 
     */
    public function getNote() 
    {
        return $this->note;
    }    
    
    /**
     * @param Customer $customer
     * @return File
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
     * @param Action $actions
     * @return File
     */
    public function addAction(Action $actions) 
    {
        $this->actions[] = $actions;
        return $this;
    }

    /**
     * @param Action $actions
     */
    public function removeAction(Action $actions) 
    {
        $this->actions->removeElement($actions);
    }

    /**
     * Get actions
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActions() 
    {
        return $this->actions;
    }

}
