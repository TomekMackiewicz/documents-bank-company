<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\File;

class FileRepository extends EntityRepository 
{
    public function getAllFiles($term, $customer)
    {
        return $this->getEntityManager()->createQuery(
            "SELECT f.signature
             FROM App:File f 
             WHERE f.signature LIKE :signature 
             AND f.customer = :customer
             ORDER BY f.signature"
        )->setParameter(":signature", '%'.$term.'%')
         ->setParameter(":customer", $customer)
         ->setMaxResults(10)
         ->getArrayResult();        
    }

    public function filesByType()
    {
        return $this->getEntityManager()->createQuery("
            SELECT
             SUM(CASE WHEN f.status = :in THEN 1 ELSE 0 END) AS in,
             SUM(CASE WHEN f.status = :out THEN 1 ELSE 0 END) AS out,
             SUM(CASE WHEN f.status = :disposed THEN 1 ELSE 0 END) AS disposed,
             SUM(CASE WHEN f.status = :unknown THEN 1 ELSE 0 END) AS unknown,
             COUNT(f.id) AS all
             FROM App:File f
        ")
        ->setParameter(":in", File::$statusIn)
        ->setParameter(":out", File::$statusOut)
        ->setParameter(":unknown", File::$statusUnknown)
        ->setParameter(":disposed", File::$statusDisposed)
        ->getSingleResult()
        ;
    }     
    
    public function filesIn() 
    {
        $filesIn = $this->getEntityManager()->createQuery(
            "SELECT count(f.id) FROM App:File f WHERE f.status=:status"
        )->setParameter(":status", File::$statusIn)->getSingleScalarResult();
        
        return $filesIn;
    }

    public function filesOut() 
    {
        $filesOut = $this->getEntityManager()->createQuery(
            "SELECT count(f.id) FROM App:File f WHERE f.status=:status"
        )->setParameter(":status", File::$statusIn)->getSingleScalarResult();
        
        return $filesOut;
    }

    public function searchFiles($searchCriteria)
    {
        $ids = [];
        
        if ($searchCriteria['customer'] instanceof \Doctrine\Common\Collections\ArrayCollection) {
            $customers = $searchCriteria['customer']->toArray();            
            foreach ($customers as $customer) {
                $ids[] = $customer->getId();
            }            
        } else {
            $ids[] = $searchCriteria['customer']->getId();
        }     
        
        $qb = $this->_em->createQueryBuilder();
        $qb->select('f')->from('App:File', 'f');

        if ($searchCriteria['signature']) {
            $qb->andWhere('f.signature LIKE :signature')
                ->setParameter(":signature", '%'.$searchCriteria['signature'].'%');
        }
        if (!empty($searchCriteria['status'])) {
            $qb->andWhere('f.status IN(:statuses)')
                ->setParameter(":statuses", $searchCriteria['status']);
        }
        if (!empty($ids)) {
            $qb->andWhere('f.customer IN(:customers)')
               ->setParameter(":customers", $ids);
        }
        
        $qb->orderBy('f.signature', 'ASC')->setMaxResults(100);
        
        return $qb->getQuery()->getResult();           
    }
    
    public function checkFileAlreadyExists($signature, $customer)
    {
        return $this->getEntityManager()->createQuery(
            "SELECT f.id FROM App:File f WHERE f.signature = :signature AND f.customer = :customer"
        )->setParameter(":signature", $signature)
         ->setParameter(":customer", $customer)
         ->getOneOrNullResult();        
    }

    public function getOrphanedTransfers($file)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('t')
            ->from('App:Transfer', 't')
            ->Where(':file MEMBER OF t.files')
            ->andWhere('SIZE(t.files)=1')
            ->groupBy('t.id')
            ->setParameter(":file", $file);
        
        return $qb->getQuery()->getResult();
    }

}
