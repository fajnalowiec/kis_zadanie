<?php

declare(strict_types=1);

namespace App\Tests\Unit\Processor;

use ApiPlatform\Metadata\Post;
use App\Entity\Author;
use App\Entity\Book;
use App\Entity\BookLoan;
use App\Entity\Customer;
use App\Processor\BorrowBookLoanProcessor;
use App\Repository\BookLoanRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class BorrowBookLoanProcessorTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private BookLoanRepository&MockObject $bookLoanRepository;
    private BorrowBookLoanProcessor $processor;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->bookLoanRepository = $this->createMock(BookLoanRepository::class);
        $this->processor = new BorrowBookLoanProcessor(
            $this->entityManager,
            $this->bookLoanRepository
        );
    }

    public function testItRejectsInvalidInput(): void
    {
        $this->entityManager->expects(self::never())->method('wrapInTransaction');
        $this->bookLoanRepository->expects(self::never())->method('isBookBorrowed');

        $this->expectException(UnprocessableEntityHttpException::class);

        $this->processor->process(new \stdClass(), new Post());
    }

    public function testItRejectsMissingBook(): void
    {
        $bookLoan = new BookLoan();
        $bookLoan->setCustomer($this->createCustomer());
        $this->mockTransaction();
        $this->bookLoanRepository->expects(self::never())->method('isBookBorrowed');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Book does not exist.');

        $this->processor->process($bookLoan, new Post());
    }

    public function testItRejectsMissingCustomer(): void
    {
        $bookLoan = new BookLoan();
        $bookLoan->setBook($this->createBook());
        $this->mockTransaction();
        $this->bookLoanRepository->expects(self::never())->method('isBookBorrowed');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Customer does not exist.');

        $this->processor->process($bookLoan, new Post());
    }

    public function testItRejectsBorrowedBook(): void
    {
        $book = $this->createBook();
        $bookLoan = $this->createBookLoan($book);
        $this->mockTransaction();

        $this->entityManager
            ->expects(self::once())
            ->method('lock')
            ->with($book, LockMode::PESSIMISTIC_WRITE);
        $this->bookLoanRepository
            ->expects(self::once())
            ->method('isBookBorrowed')
            ->with(100000)
            ->willReturn(true);
        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::never())->method('flush');

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('Book is already borrowed.');

        $this->processor->process($bookLoan, new Post());
    }

    public function testItCreatesBookLoan(): void
    {
        $book = $this->createBook();
        $bookLoan = $this->createBookLoan($book);
        $this->mockTransaction();

        $this->entityManager
            ->expects(self::once())
            ->method('lock')
            ->with($book, LockMode::PESSIMISTIC_WRITE);
        $this->bookLoanRepository
            ->expects(self::once())
            ->method('isBookBorrowed')
            ->with(100000)
            ->willReturn(false);
        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($bookLoan);
        $this->entityManager->expects(self::once())->method('flush');

        $result = $this->processor->process($bookLoan, new Post());

        self::assertSame($bookLoan, $result);
    }

    private function mockTransaction(): void
    {
        $this->entityManager
            ->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(
                fn (callable $callback): mixed => $callback($this->entityManager)
            );
    }

    private function createBookLoan(Book $book): BookLoan
    {
        $bookLoan = new BookLoan();
        $bookLoan->setBook($book);
        $bookLoan->setCustomer($this->createCustomer());

        return $bookLoan;
    }

    private function createBook(): Book
    {
        $author = new Author('William', 'Shakespeare');
        $book = new Book('Hamlet', $author);
        $this->setId($book, 100000);

        return $book;
    }

    private function createCustomer(): Customer
    {
        $customer = new Customer('John', 'Doe');
        $this->setId($customer, 100000);

        return $customer;
    }

    private function setId(object $entity, int $id): void
    {
        new ReflectionProperty($entity, 'id')->setValue($entity, $id);
    }
}
