<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class LogRepository extends EntityRepository 
{
    public function searchLog($searchCriteria)
    {       
        $qb = $this->_em->createQueryBuilder();
        $qb->select('l')->from('App:Log', 'l');

        if (!empty($searchCriteria['dateFrom'])) {
            $qb->andWhere('l.date >= :dateFrom')
               ->setParameter(":dateFrom", $searchCriteria['dateFrom']);
        }
        if (!empty($searchCriteria['dateTo'])) {
            $qb->andWhere('l.date <= :dateTo')
               ->setParameter(":dateTo", $searchCriteria['dateTo']->modify('+1 day'));
        }        
        
        $qb->orderBy('l.date', 'DESC')->setMaxResults(100);
        
        return $qb->getQuery()->getResult();           
    }   
}
