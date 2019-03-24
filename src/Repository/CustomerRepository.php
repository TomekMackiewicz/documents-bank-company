<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class CustomerRepository extends EntityRepository 
{
    public function filesInCountByCustomers() 
    {
        $filesCountByCustomers = $this->getEntityManager()->createQuery(
            "SELECT c.company, count(f.id) as fileCount 
             FROM App:Customer c 
             LEFT JOIN c.files f
             WHERE f.status=1
             GROUP BY c.company"
        )->getResult();
        
        return $filesCountByCustomers;
    }

    public function filesOutCountByCustomers() 
    {
        $filesCountByCustomers = $this->getEntityManager()->createQuery(
            "SELECT c.company, count(f.id) as filesCount 
             FROM App:Customer c 
             LEFT JOIN c.files f
             WHERE f.status=2
             GROUP BY c.company"
        )->getResult();
        
        return $filesCountByCustomers;
    }
   
    public function filesInCountByCustomer($id) 
    {
        $filesCountByCustomers = $this->getEntityManager()->createQuery(
            "SELECT c.company, count(f.id) as filesCount 
             FROM App:Customer c 
             LEFT JOIN c.files f
             WHERE f.status=1
             AND c.id=:id
             GROUP BY c.company"
        )->setParameter(':id', $id)->getResult();
        
        return $filesCountByCustomers;
    }

    public function filesOutCountByCustomer($id) 
    {
        $filesCountByCustomers = $this->getEntityManager()->createQuery(
            "SELECT c.company, count(f.id) as filesCount 
             FROM App:Customer c 
             LEFT JOIN c.files f
             WHERE f.status=2
             AND c.id=:id
             GROUP BY c.company"
        )->setParameter(':id', $id)->getResult();
        
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
            ->from('App:Customer', 'c')
            ->where('c.roles NOT LIKE :roles')
            ->setParameter('roles', '%ADMIN%');

        return $qb->getQuery()->getResult();
    }
}
