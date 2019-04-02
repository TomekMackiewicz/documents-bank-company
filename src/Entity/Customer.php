<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Customer
 *
 * @ORM\Table(name="customers")
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 * @UniqueEntity("name")
 */
class Customer 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(
     *   message = "Customer name cannot be empty."
     * )     
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="address", type="string", length=255)
     * @Assert\NotBlank(
     *   message = "Address field cannot be empty."
     * )       
     */
    private $address;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="customer")
     */
    private $users;    
    
    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="customer")
     */
    private $files;

    /**
     * @ORM\OneToMany(targetEntity="Transfer", mappedBy="customer")
     */
    private $transfers;

    /**
     * @ORM\OneToOne(targetEntity="Fee", mappedBy="customer")
     */
    private $fee;

    public function __construct() 
    {
        $this->files = new ArrayCollection();
        $this->transfers = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return integer 
     */
    public function getId() 
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return Customer
     */
    public function setName($name) 
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string 
     */
    public function getName() 
    {
        return $this->name;
    }

    /**
     * @param string $address
     * @return Customer
     */
    public function setAddress($address) 
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string 
     */
    public function getAddress() 
    {
        return $this->address;
    }

    /**
     * @param User $user
     * @return Customer
     */
    public function addUser(User $user) 
    {
        $this->users[] = $user;
        return $this;
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user) 
    {
        $this->users->removeElement($user);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers() 
    {
        return $this->users;
    }    
    
    /**
     * @param File $file
     * @return Customer
     */
    public function addFile(File $file) 
    {
        $this->files[] = $file;
        return $this;
    }

    /**
     * @param File $file
     */
    public function removeFile(File $file) 
    {
        $this->files->removeElement($file);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiles() 
    {
        return $this->files;
    }

    /**
     * @param Transfer $transfer
     * @return Customer
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

    /**
     * @param Fee $fee
     * @return Customer
     */
    public function setFee(Fee $fee = null) 
    {
        $this->fee = $fee;
        return $this;
    }

    /**
     * @return Fee
     */
    public function getFee() 
    {
        return $this->fee;
    }
  
}
