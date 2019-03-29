<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FileControllerTest extends WebTestCase
{
    
    public function testShowFilesIfNotLoggedAsAdmin()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/file/');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
    
    public function testShowFilesIfLoggedAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'Tomek',
            'PHP_AUTH_PW'   => 'tompo',
        ]);

        $client->request('GET', '/admin/file/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }       
    
    public function testShowFileIfNotLoggedAsAdmin()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/file/1');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
    
    public function testShowFileIfLoggedAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'Tomek',
            'PHP_AUTH_PW'   => 'tompo',
        ]);

        $client->request('GET', '/admin/file/1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAddNewFileIfNotLoggedAsAdmin()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/file/new');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());       
    }    
    
    public function testAddNewFileIfLoggedAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'Tomek',
            'PHP_AUTH_PW'   => 'tompo',
        ]);
        
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/admin/file/new');
        
        $form = $crawler->selectButton('Create')->form();
        $form['app_file[signature]'] = $this->generateRandomString();
        $form['app_file[status]'] = 1;
        $form['app_file[note]'] = 'Sample note';
        $form['app_file[user]'] = 2;
        
        $client->submit($form);
        
	$this->assertContains(
    	    'New file(s) created',
    	    $client->getResponse()->getContent()
	);
    }

    public function testEditNewFileIfLoggedAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'Tomek',
            'PHP_AUTH_PW'   => 'tompo',
        ]);
        
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/admin/file/2/edit');
        
        $form = $crawler->selectButton('Edit')->form();
        $form['app_file[signature]'] = $this->generateRandomString();
        $form['app_file[status]'] = 1;
        $form['app_file[note]'] = 'Sample note';
        $form['app_file[user]'] = 2;
        
        $client->submit($form);
        
	$this->assertContains(
    	    'File edited successfully',
    	    $client->getResponse()->getContent()
	);
    }    
    
    public function testInvalidSignature()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'Tomek',
            'PHP_AUTH_PW'   => 'tompo',
        ]);
        
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/admin/file/new');
        
        $form = $crawler->selectButton('Create')->form();
        $form['app_file[signature]'] = 'A2#';
        $form['app_file[status]'] = 1;
        $form['app_file[note]'] = 'Sample note';
        $form['app_file[user]'] = 2;
        
        $client->submit($form);
        
	$this->assertContains(
    	    'Only letters and digits are allowed',
    	    $client->getResponse()->getContent()
	);
    }
    
    public function testEmptySignature()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'Tomek',
            'PHP_AUTH_PW'   => 'tompo',
        ]);
        
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/admin/file/new');
        
        $form = $crawler->selectButton('Create')->form();
        $form['app_file[signature]'] = '';
        $form['app_file[status]'] = 1;
        $form['app_file[note]'] = 'Sample note';
        $form['app_file[user]'] = 2;
        
        $client->submit($form); 
        
	$this->assertContains(
    	    'Signature cannot be empty',
    	    $client->getResponse()->getContent()
	);
    }

    public function testUniqueSignature()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'Tomek',
            'PHP_AUTH_PW'   => 'tompo',
        ]);
        
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/admin/file/new');
        
        $form = $crawler->selectButton('Create')->form();
        $form['app_file[signature]'] = 'A1';
        $form['app_file[status]'] = 1;
        $form['app_file[note]'] = 'Sample note';
        $form['app_file[user]'] = 2;
        
        $client->submit($form);
        
	$this->assertContains(
    	    'File with this signature already exists',
    	    $client->getResponse()->getContent()
	);
    }
    
    private function generateRandomString($length = 10) 
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}
