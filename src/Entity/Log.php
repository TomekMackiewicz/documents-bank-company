<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log
 *
 * @ORM\Table(name="logs")
 * @ORM\Entity(repositoryClass="App\Repository\LogRepository")
 */
class Log 
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="action", type="string")  
     */    
    private $action;

    /**
    * @var string
    * @ORM\Column(name="user", type="string")  
    */   
    private $user;

    /**
    * @var string
    * @ORM\Column(name="content", type="string")  
    */   
    private $content;    
    
    /**
     * @var \Date
     * @ORM\Column(name="date", type="datetime")    
     */    
    private $date;
    
    public function __construct($action, $user, $content, $date)
    {
        $this->setAction($action);
        $this->setUser($user);
        $this->setContent($content);
        $this->setDate($date);        
    }

    /**
     * @return integer 
     */
    public function getId() 
    {
        return $this->id;
    }

    /**
     * @return string 
     */
    public function getAction() 
    {
        return $this->action;
    }

    /**
     * @param string $action
     * @return Log
     */
    public function setAction($action) 
    {
        $this->action = $action;
        return $this;
    }     
    
    /**
     * @return string 
     */
    public function getUser() 
    {
        return $this->user;
    }

    /**
     * @param string $user
     * @return Log
     */
    public function setUser($user) 
    {
        $this->user = $user;
        return $this;
    }    

    /**
     * @return string 
     */
    public function getContent() 
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return Log
     */
    public function setContent($content) 
    {
        $this->content = $content;
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
     * @param \Date $date
     * @return Log
     */
    public function setDate($date) 
    {
        $this->date = $date;
        return $this;
    }
  
}