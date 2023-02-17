<?php

namespace App\Repository;

use App\Entity\Attributes;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }
    public function findOneOrCreate(string $category): Category
    {
        $catEntity = $this->findOneBy(['name' => $category]);
        if(!$catEntity instanceof Category) {
            $catEntity = new Category();
            $catEntity->setName($category);
        }
        return $catEntity;
    }
}
