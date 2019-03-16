<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="actions")
 * @ORM\Entity(repositoryClass="App\Repository\ActionRepository")
 */
class Action 
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
     * @ORM\Column(name="action", type="string", length=8) 
     * @Assert\NotBlank(
     *   message = "Action cannot be empty."
     * ) 
     */
    private $action;

    /**
     * @var \Date
     * @ORM\Column(name="date", type="date")    
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="actions")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $customer;

    /**
     * @ORM\ManyToOne(targetEntity="File", inversedBy="actions")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $file;

    /**
     * @return integer 
     */
    public function getId() 
    {
          return $this->id;
    }

    /**
     * @param \Date $date
     * @return History
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
     * @param string $action
     * @return Action
     */
    public function setAction($action) 
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string 
     */
    public function getAction() 
    {
        return $this->action;
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
     * @return Action
     */
    public function setFile(File $file = null) 
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return File
     */
    public function getFile() 
    {
      return $this->file;
    }

}
