<?php

namespace App\Repository;

use App\Entity\Attributes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Attributes>
 *
 * @method Attributes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attributes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attributes[]    findAll()
 * @method Attributes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attributes::class);
    }

    public function findOneOrCreate(string $attribute): Attributes
    {
        $attrEntity = $this->findOneBy(['attribut' => $attribute]);
        if(!$attrEntity instanceof Attributes) {
            $attrEntity = new Attributes();
            $attrEntity->setAttribut($attribute);
        }
        return $attrEntity;
    }

}
