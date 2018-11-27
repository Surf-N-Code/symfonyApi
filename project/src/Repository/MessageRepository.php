<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

     /**
      * @return Message[] Returns an array of Message objects
      */
    public function findPostedInLast24Hours()
    {
        return $this->createQueryBuilder('m')
            ->where('m.postedOn > :dayAgo')
            ->setParameter('dayAgo', $this->getDayAgo())
            ->orderBy('m.postedOn', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Message
     */
    public function findByIdAndPostedInLast24Hours($messId)
    {

        return $this->createQueryBuilder('m')
            ->where('m.postedOn > :dayAgo')
            ->andWhere('m.id = :messId')
            ->setParameter('dayAgo', $this->getDayAgo())
            ->setParameter('messId', $messId)
            ->orderBy('m.postedOn', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
            ;
    }

    private function getDayAgo() {
        $now = new \DateTime('now');
        $dayAgo = $now->modify('-1 day');
        return $dayAgo;
    }

    /*
    public function findOneBySomeField($value): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
