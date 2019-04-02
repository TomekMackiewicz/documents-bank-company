<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 
 * @ORM\Table(name="fees")
 * @ORM\Entity(repositoryClass="App\Repository\FeeRepository")
 * @UniqueEntity("user")
 */
class Fee 
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="delivery", type="decimal", precision=9, scale=2)
     * @Assert\Regex(
     *     pattern = "/^[0-9.]*$/",
     *     match = true,
     *     message = "Only digits and dot are allowed"
     * ) 
     */
    private $delivery;

    /**
     * @var int
     * @ORM\Column(name="import", type="decimal", precision=9, scale=2)
     * @Assert\Regex(
     *     pattern = "/^[0-9.]*$/",
     *     match = true,
     *     message = "Only digits and dot are allowed"
     * ) 
     */
    private $import;

    /**
     * @var int
     * @ORM\Column(name="storage", type="decimal", precision=9, scale=2)
     * @Assert\Regex(
     *     pattern = "/^[0-9.]*$/",
     *     match = true,
     *     message = "Only digits and dot are allowed"
     * ) 
     */
    private $storage;

    /**
     * @var int
     * @ORM\Column(name="box_price", type="decimal", precision=9, scale=2)
     * @Assert\Regex(
     *     pattern = "/^[0-9.]*$/",
     *     match = true,
     *     message = "Only digits and dot are allowed"
     * ) 
     */
    private $boxPrice;    
    
    /**
     * @ORM\OneToOne(targetEntity="Customer", inversedBy="fee")
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="id", onDelete="CASCADE")
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
     * @param integer $delivery
     * @return Fee
     */
    public function setDelivery($delivery) 
    {
        $this->delivery = $delivery;
        return $this;
    }

    /**
     * @return integer 
     */
    public function getDelivery() 
    {
        return $this->delivery;
    }

    /**
     * @param integer $import
     * @return Fee
     */
    public function setImport($import) 
    {
        $this->import = $import;
        return $this;
    }

    /**
     * @return integer 
     */
    public function getImport() 
    {
        return $this->import;
    }

    /**
     * @param integer $storage
     * @return Fee
     */
    public function setStorage($storage) 
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return integer 
     */
    public function getStorage() 
    {
        return $this->storage;
    }

    /**
     * @param integer $boxPrice
     * @return Fee
     */
    public function setBoxPrice($boxPrice) 
    {
        $this->boxPrice = $boxPrice;
        return $this;
    }

    /**
     * @return integer 
     */
    public function getBoxPrice() 
    {
        return $this->boxPrice;
    }

    /**
     * @param Customer $customer
     * @return Fee
     */
    public function setUser(Customer $customer = null)
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
  
}
