<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class FileControllerTest extends WebTestCase
{   
    public static function setUpBeforeClass(): void
    {
        self::buildDb();
    }

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

    public function testEditNewFileIfLoggedAsAdmin()
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
    
    public function testShowFilesIfNotLoggedAsAdmin()
    {
        $client = static::createClient();
    
        $client->request('GET', '/admin/file/');
    
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
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
    
    public function testShowFileIfNotLoggedInAsAdmin()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/file/1');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());       
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

    private function buildDb()
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
