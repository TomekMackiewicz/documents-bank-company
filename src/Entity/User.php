<?php

namespace App\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends BaseUser 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="users")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id")    
     */
    private $customer;

    /**
     * @return integer 
     */
    public function getId() 
    {
        return $this->id;
    }

    /**
     * @param string $customer
     * @return User
     */
    public function setCustomer($customer) 
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return string 
     */
    public function getCustomer() 
    {
        return $this->customer;
    }
  
}
