<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository 
{
    public function filesInCountByUsers() 
    {
        $filesCountByUsers = $this->getEntityManager()->createQuery(
            "SELECT c.company, count(f.id) as fileCount 
             FROM App:User c 
             LEFT JOIN c.files f
             WHERE f.status=1
             GROUP BY c.company"
        )->getResult();
        
        return $filesCountByUsers;
    }

    public function filesOutCountByUsers() 
    {
        $filesCountByUsers = $this->getEntityManager()->createQuery(
            "SELECT c.company, count(f.id) as filesCount 
             FROM App:User c 
             LEFT JOIN c.files f
             WHERE f.status=2
             GROUP BY c.company"
        )->getResult();
        
        return $filesCountByUsers;
    }
   
    public function filesInCountByUser($id) 
    {
        $filesCountByUsers = $this->getEntityManager()->createQuery(
            "SELECT c.company, count(f.id) as filesCount 
             FROM App:User c 
             LEFT JOIN c.files f
             WHERE f.status=1
             AND c.id=:id
             GROUP BY c.company"
        )->setParameter(':id', $id)->getResult();
        
        return $filesCountByUsers;
    }

    public function filesOutCountByUser($id) 
    {
        $filesCountByUsers = $this->getEntityManager()->createQuery(
            "SELECT c.company, count(f.id) as filesCount 
             FROM App:User c 
             LEFT JOIN c.files f
             WHERE f.status=2
             AND c.id=:id
             GROUP BY c.company"
        )->setParameter(':id', $id)->getResult();
        
        return $filesCountByUsers;
    }

    /**
     * @param string $role
     * @return array
     */
    public function excludeAdmin()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('c')
            ->from('App:User', 'c')
            ->where('c.roles NOT LIKE :roles')
            ->setParameter('roles', '%ADMIN%');

        return $qb->getQuery()->getResult();
    }
}
