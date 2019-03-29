<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="files")
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 * @UniqueEntity(
 *     fields={"signature", "user"},
 *     message="File with this signature already exists"
 * )
 */
class File 
{
    static $statusDisposed = 0;
    static $statusIn = 1;
    static $statusOut = 2;
    static $statusUnknown = 3;
    
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
     *     pattern = "/^[a-zA-Z0-9, ]*$/",
     *     match = true,
     *     message = "Only letters and digits are allowed"
     * )  
     */
    private $signature;

    /**
     * @var string
     * @ORM\Column(name="status", type="integer")
     * @Assert\NotBlank(
     *   message = "Status cannot be empty."
     * )
     * @Assert\Choice(
     *   choices = { 0, 1, 2, 3 },
     *   message = "Choose a valid value."
     * )        
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(name="note", type="text", nullable=true)       
     */
    private $note;    
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="files")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotBlank(
     *   message = "User field cannot be empty."
     * )    
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Transfer", mappedBy="files")
     */
    private $transfers;

    public function __construct() 
    {
        $this->transfers = new ArrayCollection();
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
     * @param User $user
     * @return File
     */
    public function setUser(User $user = null) 
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return User 
     */
    public function getUser() 
    {
        return $this->user;
    }

    /**
     * @param Transfer $transfer
     * @return File
     */
    public function addTransfer(Transfer $transfer) 
    {
        $this->transfers[] = $transfer;
        return $this;
    }

    /**
     * @param Transfer $transfer
     */
    public function removeTransfer(Transfer $transfer) 
    {
        $this->transfers->removeElement($transfer);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTransfers() 
    {
        return $this->transfers;
    }

}
