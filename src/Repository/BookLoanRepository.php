<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\BookLoan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookLoan>
 */
class BookLoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookLoan::class);
    }

    public function isBookBorrowed(
        int $bookId,
        ?BookLoan &$latestBookLoan = null
    ): bool {
        $latestId = $this->createQueryBuilder('bookLoan')
            ->select('MAX(bookLoan.id)')
            ->where('IDENTITY(bookLoan.book) = :bookId')
            ->setParameter('bookId', $bookId)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);

        if ($latestId === null) {
            $latestBookLoan = null;

            return false;
        }

        $latestBookLoan = $this->find((int) $latestId);

        return $latestBookLoan?->getReturnedAt() === null;
    }
}
