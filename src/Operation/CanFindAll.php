<?php 

namespace AlexandreBulete\DddDoctrineBridge\Operation;

use Doctrine\ORM\EntityManagerInterface;

trait CanFindAll
{
    protected EntityManagerInterface $em;
    
    /**
     * @return object[]
     */
    protected function findAllEntities(): array
    {
        return $this->query()
            ->getQuery()
            ->getResult();
    }
}