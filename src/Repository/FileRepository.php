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
        $customers = $searchCriteria['customer']->toArray();
        
        $ids = [];
        foreach ($customers as $customer) {
            $ids[] = $customer->getId();
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
        
        $qb->orderBy('f.signature', 'ASC');
        
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

    public function checkEmptyTransfers($fileId)
    {
        //$qb = $this->_em->createQueryBuilder();
        //$qb->select('t')->from('App:Transfer', 't')->where('t.files is empty');
        //return $qb->getQuery()->getResult();

//Select binaryid from binarycollection group by binaryid having count(*)=1
        

        $qb = $this->_em->createQueryBuilder();
        $qb->select('IDENTITY(t.id)')->from('App:Transfer', 't')->groupby('t.id')->having('t.files =1');
        return $qb->getQuery()->getResult();
        
//        return $this->getEntityManager()->createQuery("
//            SELECT t FROM App:Transfer t
//            WHERE COUNT(t.files) = 1
//            GROUP BY t.id
//        ")
//         ->getResult();        
        
//        return $this->getEntityManager()->createQuery("
//            SELECT t FROM App:Transfer t
//            LEFT JOIN t.files f
//            WHERE :id MEMBER OF t.files
//            AND COUNT(t.files) = 1
//            GROUP BY t.id
//        ")->setParameter(":id", $fileId)
//         ->getResult();         
    }


//$query = 'SELECT o FROM IndexBundle:Offer o '.
//'LEFT JOIN o.areas a '.
//'WHERE a.id = :areaId '.
//'ORDER BY o.startDate ASC'; 
    
   
}
 