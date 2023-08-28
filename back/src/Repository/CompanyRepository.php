<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\LifecycleIteration;
use App\Entity\PriceHistory;

use App\Repository\LifecycleIterationRepository;
use App\Repository\PriceHistoryRepository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function save(Company $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Company $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneById(int $id): ?Company
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * Update a company's price and create a new history element for the current lifecycle iteration
     * History creation is not guaranteed as there might not be any current lifecycle iteration (unlikely)
     * or a history element already exists for this company at this lifecycle iteration
     */
    public function updatePriceAndCreateHistory(Company $entity, float $newPrice, bool $flush = false)
    {
        $entity->setPrice($newPrice);

        $this->createHistory($entity);

        $this->save($entity, $flush);
    }

    /**
     * Persists (and flush if specified) the passed entity and create a new history element at current lifecycle iteration
     * History creation is not guaranteed as there might not be any current lifecycle (unlikely)
     */
    public function insertWithHistory(Company $entity, bool $flush = false)
    {
        $this->createHistory($entity);
        $this->save($entity, $flush);
    }

    public function findOneByName(string $name) : ?Company
    {
        return $this->findOneBy(["name" => $name]);
    }

    public function findAllAmount(int $amount, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('c')
        ->setFirstResult($offset)
        ->setMaxResults($amount);

        $query = $qb->getQuery();

        return $query->execute();
    }

    private function createHistory(Company $entity)
    {
        /** @var LifecycleIteration */
        $currentLifecycle = $this->getEntityManager()->getRepository(LifecycleIteration::class)->current();
        
        // No current lifecycle iteration ? this shouldn't happen
        if ($currentLifecycle == null) return;

        /** @var PriceHistoryRepository */
        $priceHistoryRepos = $this->getEntityManager()->getRepository(PriceHistory::class);
        $priceHistory = $priceHistoryRepos->findHistoryFor($entity, $currentLifecycle);

        // We don't want to override an already existing price history
        if ($priceHistory) return;

        $priceHistoryRepos->insertNewHistory($entity, $currentLifecycle);
    }

    public function lastTwoPrices(Company $entity): array
    {
        $previousPrices = $entity->getPreviousPrices();
        $count = count($previousPrices->toArray());

        if ($count < 2)
        {
            return [$entity->getPrice(), $entity->getPrice()];
        }
        else
        {
            return array_values($previousPrices->slice($count - 2, 2));
        }
    }

//    /**
//     * @return Company[] Returns an array of Company objects
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

//    public function findOneBySomeField($value): ?Company
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
