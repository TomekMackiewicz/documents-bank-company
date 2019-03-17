<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class CustomerRepository extends EntityRepository 
{
    public function filesInCountByCustomers() 
    {
        $filesCountByCustomers = $this->getEntityManager()->createQuery(
            "SELECT c.name, count(b.id) as fileCount 
             FROM App:Customer c 
             LEFT JOIN c.files b
             WHERE b.status='In'
             GROUP BY c.name"
        )->getResult();
        
        return $filesCountByCustomers;
    }

    public function filesOutCountByCustomers() 
    {
        $filesCountByCustomers = $this->getEntityManager()->createQuery(
            "SELECT c.name, count(b.id) as filesCount 
             FROM App:Customer c 
             LEFT JOIN c.files b
             WHERE b.status='Out'
             GROUP BY c.name"
        )->getResult();
        
        return $filesCountByCustomers;
    }
// @Fixme set params
//$query->setParameter(':orgID', $orgID);
//$count = $query->getSingleScalarResult();    
    public function filesInCountByCustomer($id) 
    {
        $filesCountByCustomers = $this->getEntityManager()->createQuery(
            "SELECT c.name, count(b.id) as filesCount 
             FROM App:Customer c 
             LEFT JOIN c.files b
             WHERE b.status='In'
             AND c.id=$id
             GROUP BY c.name"
        )->getResult();
        
        return $filesCountByCustomers;
    }
// @Fixme set params
    public function filesOutCountByCustomer($id) 
    {
        $filesCountByCustomers = $this->getEntityManager()->createQuery(
            "SELECT c.name, count(b.id) as filesCount 
             FROM App:Customer c 
             LEFT JOIN c.files b
             WHERE b.status='Out'
             AND c.id=$id
             GROUP BY c.name"
        )->getResult();
        
        return $filesCountByCustomers;
    }

    /**
     * @param string $role
     * @return array
     */
    public function excludeAdmin()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c')
            ->from($this->_entityName, 'c')
            ->where('c.roles = :roles')
            ->setParameter('roles', 'a:1:{i:0;s:9:"ROLE_USER";}');

        return $qb->getQuery()->getResult();
    }
}
