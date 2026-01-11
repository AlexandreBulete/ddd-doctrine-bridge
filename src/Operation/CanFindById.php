<?php 

namespace AlexandreBulete\DddDoctrineBridge\Operation;

use Doctrine\ORM\EntityManagerInterface;

trait CanFindById
{
    protected EntityManagerInterface $em;
    
    /**
     * @template T of object
     * @param class-string<T> $entityClass
     * @return T|null
     */
    protected function findEntityById(string $entityClass, string $id): ?object
    {
        return $this->em->find($entityClass, $id);
    }
}