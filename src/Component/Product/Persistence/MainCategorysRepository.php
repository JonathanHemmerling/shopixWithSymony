<?php

namespace App\Component\Product\Persistence;

use App\Entity\MainCategorys;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MainCategorys>
 *
 * @method MainCategorys|null find($id, $lockMode = null, $lockVersion = null)
 * @method MainCategorys|null findOneBy(array $criteria, array $orderBy = null)
 * @method MainCategorys[]    findAll()
 * @method MainCategorys[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MainCategorysRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MainCategorys::class);
    }

    public function save(MainCategorys $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MainCategorys $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
