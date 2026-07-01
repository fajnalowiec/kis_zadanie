<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class BookLoan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Book $book;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Customer $customer;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $borrowedAt;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $returnedAt = null;

    public function __construct(Book $book, Customer $customer, DateTimeImmutable $borrowedAt)
    {
        $this->book = $book;
        $this->customer = $customer;
        $this->borrowedAt = $borrowedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): Book
    {
        return $this->book;
    }

    public function setBook(Book $book): self
    {
        $this->book = $book;

        return $this;
    }

    public function getCustomer(): Customer
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
