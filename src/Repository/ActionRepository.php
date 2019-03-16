<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class ActionRepository extends EntityRepository 
{
    public function lastActions() 
    {
        $lastActions = $this->getEntityManager()->createQuery(
            "SELECT a 
             FROM App:Action a 
             ORDER BY a.date DESC"
        )->setMaxResults(5)->getResult();
        
        return $lastActions;		
    }
// @Fixme set params
    public function customerActionsFromTo($id, $from, $to) 
    {
        $actionsFromTo = $this->getEntityManager()->createQuery(
            "SELECT a 
             FROM App:Action a 
             WHERE a.customer = '$id' 
             AND a.date BETWEEN '$from' and '$to' 
             ORDER BY a.date DESC"
        )->getResult();
        
        return $actionsFromTo;
    }
// @Fixme set params
    public function fileActionsFromTo($id, $from, $to) 
    {
        $actionsFromTo = $this->getEntityManager()->createQuery(
            "SELECT a FROM App:Action a 
             WHERE a.file = '$id' 
             AND a.date BETWEEN '$from' and '$to' 
             ORDER BY a.date DESC"
        )->getResult();
        
        return $actionsFromTo;
    }

}
