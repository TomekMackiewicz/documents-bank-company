<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\File;

class UserRepository extends EntityRepository 
{
    public function filesInCountByUsers() 
    {
        $filesCountByUsers = $this->getEntityManager()->createQuery(
            "SELECT u.company, COUNT(f.id) as fileCount 
             FROM App:User u 
             LEFT JOIN u.files f
             WHERE f.status=:status
             GROUP BY u.company"
        )->setParameter(":status", File::$statusIn)->getResult();
        
        return $filesCountByUsers;
    }

    public function filesOutCountByUsers() 
    {
        $filesCountByUsers = $this->getEntityManager()->createQuery(
            "SELECT u.company, COUNT(f.id) as filesCount 
             FROM App:User u 
             LEFT JOIN u.files f
             WHERE f.status=:status
             GROUP BY u.company"
        )->setParameter(":status", File::$statusOut)->getResult();
        
        return $filesCountByUsers;
    }
   
    public function filesInCountByUser($id) 
    {
        $filesCountByUsers = $this->getEntityManager()->createQuery(
            "SELECT u.company, COUNT(f.id) as filesCount 
             FROM App:User u 
             LEFT JOIN u.files f
             WHERE f.status=:status
             AND u.id=:id
             GROUP BY u.company"
        )->setParameter(":status", File::$statusIn)
         ->setParameter(':id', $id)->getResult();
        
        return $filesCountByUsers;
    }

    public function filesOutCountByUser($id) 
    {
        $filesCountByUsers = $this->getEntityManager()->createQuery(
            "SELECT u.company, COUNT(f.id) as filesCount 
             FROM App:User u 
             LEFT JOIN u.files f
             WHERE f.status=:status
             AND u.id=:id
             GROUP BY u.company"
        )->setParameter(":status", File::$statusOut)
         ->setParameter(':id', $id)->getResult();
        
        return $filesCountByUsers;
    }

    /**
     * @param string $role
     * @return array
     */
    public function excludeAdmin()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('u')
            ->from('App:User', 'u')
            ->where('u.roles NOT LIKE :roles')
            ->setParameter('roles', '%ADMIN%');

        return $qb->getQuery()->getResult();
    }
}
