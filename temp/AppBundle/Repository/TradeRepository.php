<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TradeRepository extends EntityRepository
{
    /**
     * This function will be called by searchAction and return search results.
     */
    public function findTradesByBankID($behavior, $id, $from, $to)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT t FROM AppBundle:Trade t
             WHERE t.behavior = :behavior
             AND t.bank = :bid
             ORDER BY t.id DESC'
        )
        ->setParameter('behavior', $behavior)
        ->setParameter('bid',$id)
        ->setFirstResult($from)
        ->setMaxResults($to - $from + 1);
         $result = $query->getResult();

         return $result;
    }
}
