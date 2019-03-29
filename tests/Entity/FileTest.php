<?php

namespace App\Tests\Entity;

use App\Entity\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function testSetSignature()
    {
        $file = new File();
        $result = $file->setSignature('A1');

        $this->assertEquals($file, $result);
    }
}
