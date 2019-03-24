<?php

namespace App\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Customer
 *
 * @ORM\Table(name="customers")
 * @ORM\Entity(repositoryClass="App\Repository\CustomerRepository")
 * @UniqueEntity("username")
 */
class Customer extends BaseUser 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="company", type="string", length=64, nullable=true)
     * @Assert\NotBlank(
     *   message = "Company field cannot be empty."
     * )     
     */
    private $company;

    /**
     * @var string
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     * @Assert\NotBlank(
     *   message = "Address field cannot be empty."
     * )       
     */
    private $address;

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
        parent::__construct();
        $this->files = new ArrayCollection();
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
     * @param string $company
     * @return Customer
     */
    public function setCompany($company) 
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return string 
     */
    public function getCompany() 
    {
        return $this->company;
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
