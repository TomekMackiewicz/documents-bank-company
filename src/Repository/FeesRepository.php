<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class FeesRepository extends EntityRepository 
{
    public function actionsToCalculate($id, $from, $to) 
    {
// @Fixme set params        
        // Liczba usług
        $actionsQuery = $this->getEntityManager()->createQuery(
            "SELECT a.action FROM App:Action a WHERE a.customer = $id AND a.date BETWEEN '$from' and '$to'"
        )->getResult();

        // Lista opłat
        $feesQuery = $this->getEntityManager()->createQuery(
                "SELECT f.delivery,f.import,f.storage FROM App:Fees f WHERE f.customer = $id"
        )->getResult();

        $fees = $feesQuery[0];

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

        return $feesTable = array_merge($fees, $numberOfActions);
    }

}
