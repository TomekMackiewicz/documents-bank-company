<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

class TransferRepository extends EntityRepository 
{
    public function lastTransfers() 
    {
        return $this->getEntityManager()->createQuery(
            "SELECT t 
             FROM App:Transfer t 
             ORDER BY t.date DESC"
        )->setMaxResults(5)->getResult();		
    }

    public function fileTransfersFromTo($id, $from, $to) 
    {
        return $this->getEntityManager()->createQuery(
            "SELECT t FROM App:Transfer t 
             WHERE :id MEMBER OF t.files
             AND t.date BETWEEN :from and :to
             ORDER BY t.date DESC"
        )->setParameter(':id', $id)
         ->setParameter(':from', $from)
         ->setParameter(':to', $to)
         ->getResult();
    }

    public function searchTransfers($searchCriteria)
    {
        $ids = [];
        
        if ($searchCriteria['user'] instanceof ArrayCollection) {
            $users = $searchCriteria['user']->toArray();
            
            foreach ($users as $user) {
                $ids[] = $user->getId();
            }            
        } else {
            $ids[] = $searchCriteria['user']->getId();
        }
        
        $qb = $this->_em->createQueryBuilder();
        $qb->select('t')->from('App:Transfer', 't');

        if (!empty($searchCriteria['type'])) {
            $qb->andWhere('t.type IN(:types)')
               ->setParameter(":types", $searchCriteria['type']);
        }
        if (!empty($searchCriteria['dateFrom'])) {
            $qb->andWhere('t.date > :dateFrom')
               ->setParameter(":dateFrom", $searchCriteria['dateFrom']);
        }
        if (!empty($searchCriteria['dateTo'])) {
            $qb->andWhere('t.date < :dateTo')
               ->setParameter(":dateTo", $searchCriteria['dateTo']);
        }        
        if (!empty($ids)) {
            $qb->andWhere('t.user IN(:users)')
               ->setParameter(":users", $ids);
        }
        
        $qb->orderBy('t.date', 'DESC');
        
        return $qb->getQuery()->getResult();           
    }     
    
}
