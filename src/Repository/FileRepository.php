<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class FileRepository extends EntityRepository 
{
    public function getAllFiles($term)
    {
        return $this->getEntityManager()->createQuery(
            "SELECT f.signature FROM App:File f WHERE f.signature LIKE :signature"
        )->setParameter(":signature", '%'.$term.'%')->setMaxResults(10)->getArrayResult();        
    }
    
    public function filesIn() 
    {
        $filesIn = $this->getEntityManager()->createQuery(
            "SELECT count(f.id) FROM App:File f WHERE f.status='In'"
        )->getSingleScalarResult();
        
        return $filesIn;
    }

    public function filesOut() 
    {
        $filesOut = $this->getEntityManager()->createQuery(
            "SELECT count(f.id) FROM App:File f WHERE f.status='Out'"
        )->getSingleScalarResult();
        
        return $filesOut;
    }
    
    public function searchFiles($searchCriteria)
    {
        $users = $searchCriteria['user']->toArray();
        
        $ids = [];
        foreach ($users as $user) {
            $ids[] = $user->getId();
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
            $qb->andWhere('f.user IN(:users)')
               ->setParameter(":users", $ids);
        }
        
        return $qb->getQuery()->getResult();           
    }
    
//    public function countFiles()
//    {
//        return $this->_em->createQuery("SELECT COUNT(f.id) FROM App:File f")->getSingleScalarResult();
//    }      
}
 