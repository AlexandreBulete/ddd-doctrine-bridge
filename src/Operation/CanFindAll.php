<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge\Operation;

use Doctrine\ORM\EntityManagerInterface;

trait CanFindAll
{
    protected EntityManagerInterface $em;
    
    /**
     * @return list<object>
     */
    protected function findAllEntities(): array
    {
        /** @var list<object> */
        return $this->query()
            ->getQuery()
            ->getResult();
    }
}