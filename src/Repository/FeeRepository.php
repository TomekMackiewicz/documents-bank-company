<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class FeeRepository extends EntityRepository 
{
    public function actionsToCalculate($id, $from, $to) 
    {
// @Fixme set params        
        // Liczba usług
        $actionsQuery = $this->getEntityManager()->createQuery(
            "SELECT t.type FROM App:Transfer t WHERE t.customer = $id AND t.date BETWEEN '$from' and '$to'"
        )->getResult();

        // Lista opłat
        $feeQuery = $this->getEntityManager()->createQuery(
                "SELECT f.delivery,f.import,f.storage FROM App:Fee f WHERE f.customer = $id"
        )->getResult();

        $fee = $feeQuery[0];

        // Liczymy usługi (ilość przywozów i dowozów)
        $numberOfActions = [];
        foreach ($actionsQuery as $value) {
            foreach ($value as $key2 => $value2) {
                $index = $key2.''.$value2;
                if (array_key_exists($index, $numberOfActions)) {
                   $numberOfActions[$index]++;
                } else {
                   $numberOfActions[$index] = 1;
                }
            }
        }

        if(!isset($numberOfActions['actionIn'])) {
           $numberOfActions['actionIn'] = 0;
        }
        if(!isset($numberOfActions['actionOut'])) {
           $numberOfActions['actionOut'] = 0;
        }    

        return $feeTable = array_merge($fee, $numberOfActions);
    }

}
