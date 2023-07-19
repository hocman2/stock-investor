<?php

namespace App\Repository;

use App\Entity\CompanyDomain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyDomain>
 *
 * @method CompanyDomain|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyDomain|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyDomain[]    findAll()
 * @method CompanyDomain[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyDomainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyDomain::class);
    }

    public function save(CompanyDomain $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CompanyDomain $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByName(string $name): ?CompanyDomain
    {
        return $this->findOneBy(["name" => $name]);
    }

//    /**
//     * @return CompanyDomain[] Returns an array of CompanyDomain objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CompanyDomain
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
