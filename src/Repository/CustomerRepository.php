<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\File;

class CustomerRepository extends EntityRepository 
{
    public function customerFilesByType($customer)
    {
        return $this->getEntityManager()->createQuery("
            SELECT
             SUM(CASE WHEN f.status = :in THEN 1 ELSE 0 END) AS in,
             SUM(CASE WHEN f.status = :out THEN 1 ELSE 0 END) AS out,
             SUM(CASE WHEN f.status = :disposed THEN 1 ELSE 0 END) AS disposed,
             SUM(CASE WHEN f.status = :unknown THEN 1 ELSE 0 END) AS unknown,
             COUNT(f.id) AS all
             FROM App:File f 
             WHERE f.customer=:customer
        ")
        ->setParameter(":in", File::$statusIn)
        ->setParameter(":out", File::$statusOut)
        ->setParameter(":unknown", File::$statusUnknown)
        ->setParameter(":disposed", File::$statusDisposed)
        ->setParameter(':customer', $customer)->getSingleResult()
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
