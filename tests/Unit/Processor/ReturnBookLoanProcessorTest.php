<?php

declare(strict_types=1);

namespace App\Tests\Unit\Processor;

use ApiPlatform\Metadata\Post;
use App\Entity\Author;
use App\Entity\Book;
use App\Entity\BookLoan;
use App\Entity\Customer;
use App\Processor\ReturnBookLoanProcessor;
use App\Repository\BookLoanRepository;
use DateTimeImmutable;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class ReturnBookLoanProcessorTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private BookLoanRepository&MockObject $bookLoanRepository;
    private ReturnBookLoanProcessor $processor;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->bookLoanRepository = $this->createMock(BookLoanRepository::class);
        $this->processor = new ReturnBookLoanProcessor(
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
        $this->mockTransaction();
        $this->bookLoanRepository->expects(self::never())->method('isBookBorrowed');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Book does not exist.');

        $this->processor->process(new BookLoan(), new Post());
    }

    public function testItRejectsBookThatIsNotBorrowed(): void
    {
        $book = $this->createBook();
        $bookLoan = new BookLoan();
        $bookLoan->setBook($book);
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
        $this->bookLoanRepository
            ->expects(self::never())
            ->method('getLatestBookLoan');
        $this->entityManager->expects(self::never())->method('flush');

        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('Book is not borrowed.');

        $this->processor->process($bookLoan, new Post());
    }

    public function testItReturnsLatestBookLoan(): void
    {
        $book = $this->createBook();
        $input = new BookLoan();
        $input->setBook($book);
        $latestBookLoan = new BookLoan();
        $latestBookLoan->setBook($book);
        $latestBookLoan->setCustomer($this->createCustomer());
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
        $this->bookLoanRepository
            ->expects(self::once())
            ->method('getLatestBookLoan')
            ->willReturn($latestBookLoan);
        $this->entityManager->expects(self::once())->method('flush');

        $result = $this->processor->process($input, new Post());

        self::assertSame($latestBookLoan, $result);
        self::assertEquals(
            new DateTimeImmutable('today'),
            $latestBookLoan->getReturnedAt()
        );
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

    private function createBook(): Book
    {
        $book = new Book(
            'Hamlet',
            new Author('William', 'Shakespeare')
        );
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
