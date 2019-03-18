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

        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')->from('App:File', 'f');

        if ($searchCriteria['signature']) {
            $qb->andWhere('f.signature LIKE :signature')
                ->setParameter(":signature", '%'.$searchCriteria['signature'].'%');
        }
        if (!empty($searchCriteria['status'])) {
            $qb->andWhere('f.status IN(:statuses)')
               ->setParameter(":statuses", $searchCriteria['status']);
        }
        if (!empty($ids)) {
            $qb->andWhere('f.customer IN(:customers)')
               ->setParameter(":customers", $ids);
        }
        
        return $qb->getQuery()->getResult();           
    }
    
//    public function countFiles()
//    {
//        return $this->_em->createQuery("SELECT COUNT(f.id) FROM App:File f")->getSingleScalarResult();
//    }      
}
 