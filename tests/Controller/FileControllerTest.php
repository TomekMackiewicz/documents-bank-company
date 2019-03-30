<?php

// ./bin/phpunit

namespace App\Tests\Controller;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class FileControllerTest extends WebTestCase
{   
    public static function setUpBeforeClass(): void
    {
        self::buildDb();
    }

    //-----------------------------------------------------
    // ADD NEW FILE
    //-----------------------------------------------------    
    
    public function testAddNewFileIfNotLoggedIn()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/file/new');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());       
    }

    public function testAddNewFileIfNotLoggedInAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'user',
        ]);

        $client->request('GET', '/admin/file/new');

        $this->assertEquals(403, $client->getResponse()->getStatusCode()); 
    }
    
    public function testAddNewFileIfLoggedInAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ]);
        
        $crawler = $client->request('GET', '/admin/file/new');
        
        $form = $crawler->selectButton('Create')->form();
        $form['app_file[signature]'] = 'A1';
        $form['app_file[status]'] = 1;
        $form['app_file[note]'] = 'Sample note';
        $form['app_file[user]'] = 2;
        
        $client->submit($form);
        
        $client->followRedirect();
        
	$this->assertContains(
    	    'New file(s) created',
    	    $client->getResponse()->getContent()
	);
    }

    //-----------------------------------------------------
    // EDIT FILE
    //-----------------------------------------------------     
    
    public function testEditNewFileIfLoggedInAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ]);
        
        $client->request('POST', '/admin/file/new');
        
        $crawler = $client->request('GET', '/admin/file/1/edit');
        
        $form = $crawler->selectButton('Edit')->form();
        $form['app_file[signature]'] = 'A2';
        $form['app_file[status]'] = 2;
        $form['app_file[note]'] = 'Sample edited note';
        $form['app_file[user]'] = 2;
        
        $client->submit($form);
        
        $client->followRedirect();
        
	$this->assertContains(
    	    'File edited successfully',
    	    $client->getResponse()->getContent()
	);
    }        

    //-----------------------------------------------------
    // SHOW FILES (INDEX)
    //----------------------------------------------------- 
    
    public function testShowFilesIfNotLoggedIn()
    {
        $client = static::createClient();
    
        $client->request('GET', '/admin/file/');
    
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testShowFilesIfNotLoggedInAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'user',
        ]);
    
        $client->request('GET', '/admin/file/');
    
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
    
    public function testShowFilesIfLoggedInAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ]);

        $client->request('GET', '/admin/file/');
    
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }       

    //-----------------------------------------------------
    // SHOW FILE
    //----------------------------------------------------- 
    
    public function testShowFileIfNotLoggedIn()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/file/1');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());       
    } 

    public function testShowFileIfNotLoggedInAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'user',
        ]);

        $client->request('GET', '/admin/file/1');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());       
    } 
    
    public function testShowFileIfLoggedInAsAdmin()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ]);

        $client->request('GET', '/admin/file/1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    //-----------------------------------------------------
    // VALIDATE SIGNATURE
    //-----------------------------------------------------
    
    public function testInvalidSignature()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ]);
        
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
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ]);
        
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
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ]);
        
        $crawler = $client->request('GET', '/admin/file/new');
        
        $form = $crawler->selectButton('Create')->form();
        $form['app_file[signature]'] = 'A2';
        $form['app_file[status]'] = 1;
        $form['app_file[note]'] = 'Sample note';
        $form['app_file[user]'] = 2;
        
        $client->submit($form);
        
	$this->assertContains(
    	    'File with this signature already exists',
    	    $client->getResponse()->getContent()
	);
    }

    //-----------------------------------------------------
    // FILE TRANSFERS SEARCH FORM
    //-----------------------------------------------------
    
    public function testShowFileTransfers()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ]);
        
        $crawler = $client->request('GET', '/admin/file/1');
        
        $dateFrom = new \DateTime();
        $dateTo = new \DateTime();        
        
        $form = $crawler->selectButton('Search')->form();
        $form['action[dateFrom]'] = $dateFrom->modify("-1 day")->format("Y-m-d");
        $form['action[dateTo]'] = $dateTo->modify("+1 day")->format("Y-m-d");
        
        $client->submit($form);
        
	$this->assertContains(
    	    'A2',
    	    $client->getResponse()->getContent()
	);        
    }

    //-----------------------------------------------------
    // FILE TRANSFERS WITH TYPE ADJUSTMENT
    //-----------------------------------------------------    

    public function testFileTransferAfterAddNewFile()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ]);

        $client->request('GET', '/admin/transfer/1');
    
	$this->assertContains(
    	    'Adjustment',
    	    $client->getResponse()->getContent()
	); 
    }

    public function testFileTransferAfterEditFile()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ]);

        $client->request('GET', '/admin/transfer/2');
    
	$this->assertContains(
    	    'Adjustment',
    	    $client->getResponse()->getContent()
	); 
    }
    
    private static function buildDb()
    {
        $kernel = new Kernel('test', true);
        $kernel->boot();

        $application = new Application($kernel);

        $application->setAutoExit(false);    

        $application->run(new ArrayInput(array(
            'doctrine:schema:drop',
            '--force' => true
        )));

        $application->run(new ArrayInput(array(
            'doctrine:schema:create'
        )));

        $application->run(new ArrayInput(array(
            'fos:user:create', 
            'username' => 'admin', 
            'email' => 'admin@test.com', 
            'password' => 'admin', 
            '--super-admin' =>true
        )));    

        $application->run(new ArrayInput(array(
            'fos:user:create', 
            'username' => 'user', 
            'email' => 'user@test.com', 
            'password' => 'user'
        )));    

    }   
}       
