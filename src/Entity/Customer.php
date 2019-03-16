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
 * @UniqueEntity("name")
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
     * @ORM\Column(name="name", type="string", length=64)
     * @Assert\NotBlank(
     *   message = "Name field cannot be empty."
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
     * @ORM\OneToMany(targetEntity="File", mappedBy="customer")
     */
    private $files;

    /**
     * @ORM\OneToMany(targetEntity="Action", mappedBy="customer")
     */
    private $actions;

    /**
     * @ORM\OneToOne(targetEntity="Fees", mappedBy="customer")
     */
    private $fee;

    public function __construct() 
    {
        parent::__construct();
        $this->files = new ArrayCollection();
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
     * @param Action $action
     * @return Customer
     */
    public function addAction(Action $action) 
    {
        $this->actions[] = $action;
        return $this;
    }

    /**
     * @param Action $action
     */
    public function removeAction(Action $action) 
    {
        $this->actions->removeElement($action);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActions() 
    {
        return $this->actions;
    }

    /**
     * @param Fees $fee
     * @return Customer
     */
    public function setFee(Fees $fee = null) 
    {
        $this->fee = $fee;
        return $this;
    }

    /**
     * @return Fees 
     */
    public function getFee() 
    {
        return $this->fee;
    }
  
}
