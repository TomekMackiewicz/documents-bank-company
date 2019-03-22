<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Fees
 * @ORM\Table(name="fees")
 * @ORM\Entity(repositoryClass="App\Repository\FeeRepository")
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
     * @ORM\Column(name="delivery", type="integer")
     */
    private $delivery;

    /**
     * @var int
     * @ORM\Column(name="import", type="integer")
     */
    private $import;

    /**
     * @var int
     * @ORM\Column(name="storage", type="integer")
     */
    private $storage;

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
     * @return Fees
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
     * @return Fees
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
     * @return Fees
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
     * @param Customer $customer
     * @return Fees
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
  
}
