<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Processor\BorrowBookLoanProcessor;
use App\Processor\ReturnBookLoanProcessor;
use App\Repository\BookLoanRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [
    new Post(
        uriTemplate: '/book-loans/borrow',
        processor: BorrowBookLoanProcessor::class,
        denormalizationContext: ['groups' => ['book_loan:borrow']],
        validationContext: ['groups' => ['book_loan:borrow']]
    ),
    new Post(
        uriTemplate: '/book-loans/return',
        status: Response::HTTP_OK,
        processor: ReturnBookLoanProcessor::class,
        denormalizationContext: ['groups' => ['book_loan:return']],
        validationContext: ['groups' => ['book_loan:return']]
    ),
])]
#[ORM\Entity(repositoryClass: BookLoanRepository::class)]
class BookLoan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(groups: ['book_loan:borrow', 'book_loan:return'])]
    #[Groups(['book_loan:borrow', 'book_loan:return'])]
    private ?Book $book = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull(groups: ['book_loan:borrow'])]
    #[Groups(['book_loan:borrow'])]
    private ?Customer $customer = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, options: ['default' => 'CURRENT_DATE'])]
    private DateTimeImmutable $borrowedAt;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $returnedAt = null;

    public function __construct()
    {
        $this->borrowedAt = new DateTimeImmutable('today');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(Book $book): self
    {
        $this->book = $book;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getBorrowedAt(): DateTimeImmutable
    {
        return $this->borrowedAt;
    }

    public function setBorrowedAt(DateTimeImmutable $borrowedAt): self
    {
        $this->borrowedAt = $borrowedAt;

        return $this;
    }

    public function getReturnedAt(): ?DateTimeImmutable
    {
        return $this->returnedAt;
    }

    public function setReturnedAt(?DateTimeImmutable $returnedAt): self
    {
        $this->returnedAt = $returnedAt;

        return $this;
    }
}
