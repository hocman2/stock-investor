<?php

namespace App\Repository;

use App\Entity\PriceHistory;
use App\Entity\Company;
use App\Entity\LifecycleIteration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PriceHistory>
 *
 * @method PriceHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceHistory[]    findAll()
 * @method PriceHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceHistory::class);
    }

    public function save(PriceHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function insertNewHistory(Company $company, LifecycleIteration $lifecycleIteration, bool $flush = false): void
    {
        $newHistory = new PriceHistory();
        $newHistory->setCompany($company);
        $newHistory->setLifecycleIteration($lifecycleIteration);

        $this->save($newHistory, $flush);
    }

    public function remove(PriceHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PriceHistory[] Returns an array of PriceHistory objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PriceHistory
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
