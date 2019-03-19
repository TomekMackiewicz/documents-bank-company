<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

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

    public function customerTransfersFromTo($id, $from, $to) 
    {
        return $this->getEntityManager()->createQuery(
            "SELECT t
             FROM App:Transfer t 
             WHERE t.customer = :id 
             AND t.date BETWEEN ':from' and ':to' 
             ORDER BY t.date DESC"
        )->setParameter(':id', $id)
         ->setParameter(':from', $from)
         ->setParameter(':to', $to)
         ->getResult();
    }

    public function fileTransfersFromTo($id, $from, $to) 
    {
        return $this->getEntityManager()->createQuery(
            "SELECT t FROM App:Transfer t 
             WHERE t.file = :id 
             AND t.date BETWEEN ':from' and ':to'
             ORDER BY t.date DESC"
        )->setParameter(':id', $id)
         ->setParameter(':from', $from)
         ->setParameter(':to', $to)
         ->getResult();
    }

    public function searchTransfers($searchCriteria)
    {
        $customers = $searchCriteria['customer']->toArray();
        
        $ids = [];
        foreach ($customers as $customer) {
            $ids[] = $customer->getId();
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
            $qb->andWhere('t.customer IN(:customers)')
               ->setParameter(":customers", $ids);
        }
        
        return $qb->getQuery()->getResult();           
    }     
    
}
