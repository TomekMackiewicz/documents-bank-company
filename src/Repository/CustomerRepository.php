<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\File;

class CustomerRepository extends EntityRepository 
{
    public function customerFilesByType($id)
    {
        return $this->getEntityManager()->createQuery("
            SELECT 
            SUM(CASE WHEN f.status = :in THEN 1 ELSE 0 END) AS in,
            SUM(CASE WHEN f.status = :out THEN 1 ELSE 0 END) AS out,
            SUM(CASE WHEN f.status = :unknown THEN 1 ELSE 0 END) AS unknown,
            SUM(CASE WHEN f.status = :disposed THEN 1 ELSE 0 END) AS disposed
            FROM App:Customer c 
            LEFT JOIN c.files f
            WHERE c.id=:id
            GROUP BY c.name
        ")
        ->setParameter(":in", File::$statusIn)
        ->setParameter(":out", File::$statusOut)
        ->setParameter(":unknown", File::$statusUnknown)
        ->setParameter(":disposed", File::$statusDisposed)
        ->setParameter(':id', $id)->getResult()
        ;
    }

//    /**
//     * @param string $role
//     * @return array
//     */
//    public function excludeAdmin()
//    {
//        $qb = $this->_em->createQueryBuilder();
//        $qb->select('u')
//            ->from('App:User', 'u')
//            ->where('u.roles NOT LIKE :roles')
//            ->setParameter('roles', '%ADMIN%');
//
//        return $qb->getQuery()->getResult();
//    }
}
