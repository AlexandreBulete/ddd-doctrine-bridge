<?php

declare(strict_types=1);

namespace AlexandreBulete\DddDoctrineBridge;

use AlexandreBulete\DddFoundation\Domain\Repository\PaginatorInterface;

/**
 * Paginator simplified that works with custom Doctrine types.
 * Contours the limitations of the Doctrine\ORM\Tools\Pagination\Paginator.
 *
 * @template T of object
 *
 * @implements PaginatorInterface<T>
 */
final readonly class DoctrinePaginator implements PaginatorInterface
{
    /**
     * @param array<T> $items
     */
    public function __construct(
        private array $items,
        private int $totalItems,
        private int $currentPage,
        private int $itemsPerPage,
    ) {
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getLastPage(): int
    {
        if ($this->itemsPerPage <= 0) {
            return 1;
        }

        return (int) ceil($this->totalItems / $this->itemsPerPage) ?: 1;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }
}

