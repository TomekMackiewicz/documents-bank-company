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
    
    public function searchFiles($searchCriteria)
    {
        $customers = $searchCriteria['customer']->toArray();
        
        $ids = [];
        foreach ($customers as $customer) {
            $ids[] = $customer->getId();
        }
        
        $customersIds = implode(',', $ids);        
        $signature = $searchCriteria['signature'];
        $statuses = implode(',', $searchCriteria['status']);
        
        $query = "SELECT f FROM App:File f WHERE f.note IS NULL ";
        if ($signature) {
            $query .= "AND f.signature LIKE :signature ";
        }
        if ($statuses) {
            $query .= "AND f.status IN(:statuses) ";
        }
        if ($customersIds) {
            $query .= "AND f.customer IN(:customersIds)";
        }
        
        $files = $this->getEntityManager()->createQuery($query)
            ->setParameter(':signature', '%'.$signature.'%')
            ->setParameter(':statuses', $statuses)
            ->setParameter(':customersIds', $customersIds)
            ->getResult();
        
        return $files;            
    }
}
 