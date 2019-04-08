<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\Transfer;

class FeeRepository extends EntityRepository 
{
    public function actionsToCalculate($id, $month) 
    {
        $result = [];
        // First day of the month
        $from = date('Y-m-01', strtotime($month));
        // Last day of the month
        $to = date('Y-m-t', strtotime($month));

        $transfersQuery = $this->getEntityManager()->createQuery(
            "SELECT 
             SUM(CASE WHEN t.type = :typeIn THEN 1 ELSE 0 END) AS import,
             SUM(CASE WHEN t.type = :typeOut THEN 1 ELSE 0 END) AS delivery,
             1 AS storage,
             SUM(t.boxes) AS boxes
             FROM App:Transfer t 
             WHERE t.customer = :id 
             AND t.date BETWEEN :from and :to
             AND t.type IN (:typeIn, :typeOut)
            "
        )
        ->setParameter(':id', $id)
        ->setParameter(':from', $from)
        ->setParameter(':to', $to)
        ->setParameter(':typeIn', Transfer::$transferIn)
        ->setParameter(':typeOut', Transfer::$transferOut)
        ->getOneOrNullResult();
        
        $result['transfers'] = $transfersQuery;

        $feesQuery = $this->getEntityManager()->createQuery(
                "SELECT f.import, f.delivery, f.storage, f.boxPrice AS boxes FROM App:Fee f WHERE f.customer = :id"
        )->setParameter(':id', $id)->getOneOrNullResult();
        
        if ($feesQuery) {
            $result['fees'] = $feesQuery;
        } else {
            return null;
        }

        $result['subtotals'] = array_map(function($x, $y) { return $x * $y; },
                   $result['transfers'], $result['fees']); 
        
        $result['total'] = array_sum($result['subtotals']);

        return $result;
    }

}
