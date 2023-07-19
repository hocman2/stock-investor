<?php

namespace App\Repository;

use App\Entity\LifecycleIteration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LifecycleIteration>
 *
 * @method LifecycleIteration|null find($id, $lockMode = null, $lockVersion = null)
 * @method LifecycleIteration|null findOneBy(array $criteria, array $orderBy = null)
 * @method LifecycleIteration[]    findAll()
 * @method LifecycleIteration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LifecycleIterationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LifecycleIteration::class);
    }

    public function save(LifecycleIteration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LifecycleIteration $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function current(): ?LifecycleIteration
    {
        $obj = $this->findBy([], ["id" => "DESC"], 1);
        return (count($obj) == 1) ? $obj[0] : null;
    }

//    /**
//     * @return LifecycleIteration[] Returns an array of LifecycleIteration objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LifecycleIteration
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
