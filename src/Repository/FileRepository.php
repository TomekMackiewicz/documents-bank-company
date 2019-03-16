<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class FileRepository extends EntityRepository 
{
    public function filesIn() 
    {
        $filesIn = $this->getEntityManager()->createQuery(
            "SELECT count(b.id) FROM App:File b WHERE b.status='In'"
        )->getSingleScalarResult();
        
        return $filesIn;
    }

    public function filesOut() 
    {
        $filesOut = $this->getEntityManager()->createQuery(
            "SELECT count(b.id) FROM App:File b WHERE b.status='Out'"
        )->getSingleScalarResult();
        
        return $filesOut;
    }
}
