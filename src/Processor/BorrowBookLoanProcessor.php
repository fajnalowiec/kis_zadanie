<?php

declare(strict_types=1);

namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\BookLoan;
use App\Repository\BookLoanRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<BookLoan, BookLoan>
 */
final readonly class BorrowBookLoanProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BookLoanRepository $bookLoanRepository,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): BookLoan {
        if (!$data instanceof BookLoan) {
            throw new UnprocessableEntityHttpException('Invalid book loan data.');
        }

        return $this->entityManager->wrapInTransaction(
            function (EntityManagerInterface $entityManager) use ($data): BookLoan {
                $book = $data->getBook();
                $customer = $data->getCustomer();

                if ($book === null || $book->getId() === null) {
                    throw new NotFoundHttpException('Book does not exist.');
                }

                if ($customer === null || $customer->getId() === null) {
                    throw new NotFoundHttpException('Customer does not exist.');
                }

                $entityManager->lock($book, LockMode::PESSIMISTIC_WRITE);

                if ($this->bookLoanRepository->isBookBorrowed($book->getId())) {
                    throw new ConflictHttpException('Book is already borrowed.');
                }

                $entityManager->persist($data);
                $entityManager->flush();

                return $data;
            }
        );
    }
}
