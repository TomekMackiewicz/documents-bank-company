<?php

namespace App\Tests\Entity;

use App\Entity\File;
use App\Entity\User;
use App\Entity\Transfer;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    private static $file;
    private static $user;
    private static $transfer;
    
    public static function setUpBeforeClass(): void
    {
        self::$file = new File();
        self::$user = new User();
        self::$user->setCompany("Sample company");
        self::$transfer = new Transfer();
    }
    
    public function testSetSignature()
    {
        $result = self::$file->setSignature('A1');
        $this->assertEquals(self::$file, $result);
    }
    
    public function testSetStatus()
    {
        $result = self::$file->setStatus(1);
        $this->assertEquals(self::$file, $result);
    } 
    
    public function testSetNote()
    {
        $result = self::$file->setNote("Sample note");
        $this->assertEquals(self::$file, $result);
    }
    
    public function testSetUser()
    {
        $result = self::$file->setUser(self::$user);
        $this->assertEquals(self::$file, $result);
    }    

    public function testAddTransfer()
    {
        $result = self::$file->addTransfer(self::$transfer);
        $this->assertEquals(self::$file, $result);
    }  

    public function testGetSignature()
    {
        $result = self::$file->getSignature();
        $this->assertEquals('A1', $result);
    }    

    public function testGetStatus()
    {
        $result = self::$file->getStatus();
        $this->assertEquals(1, $result);
    }     

    public function testGetNote()
    {
        $result = self::$file->getNote();
        $this->assertEquals("Sample note", $result);
    }  

    public function testGetUser()
    {
        $result = self::$file->getUser();
        $this->assertEquals("Sample company", $result->getCompany());
    } 

    public function testGetTransfers()
    {
        $result = self::$file->getTransfers();
        $this->assertTrue($result->contains(self::$transfer));
    } 
    
    public function testRemoveTransfer()
    {
        self::$file->removeTransfer(self::$transfer);
        $collection = self::$file->getTransfers();
        $this->assertTrue(false === $collection->contains(self::$transfer));
    }
   
}
