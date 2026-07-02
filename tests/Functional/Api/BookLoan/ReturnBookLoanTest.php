<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\BookLoan;

use App\Tests\Functional\FunctionalTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;

final class ReturnBookLoanTest extends FunctionalTestCase
{
    public function testItReturnsBorrowedBook(): void
    {
        $request = $this->createRequest(100003);

        try {
            $response = $this->kernel->handle($request);
            $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            self::assertSame(200, $response->getStatusCode());
            self::assertSame(
                new DateTimeImmutable('today')->format('Y-m-d'),
                new DateTimeImmutable($data['returnedAt'])->format('Y-m-d')
            );
        } finally {
            if (isset($response)) {
                $this->kernel->terminate($request, $response);
            }
        }
    }

    public function testItRejectsBookThatIsNotBorrowed(): void
    {
        $request = $this->createRequest(100001);

        try {
            $response = $this->kernel->handle($request);
            $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            self::assertSame(409, $response->getStatusCode());
            self::assertSame('Book is not borrowed.', $data['detail']);
        } finally {
            if (isset($response)) {
                $this->kernel->terminate($request, $response);
            }
        }
    }

    public function testItRejectsNonexistentBook(): void
    {
        $request = $this->createRequest(999999);

        try {
            $response = $this->kernel->handle($request);

            self::assertSame(400, $response->getStatusCode());
        } finally {
            if (isset($response)) {
                $this->kernel->terminate($request, $response);
            }
        }
    }

    private function createRequest(int $bookId): Request
    {
        return Request::create(
            '/api/book-loans/return',
            Request::METHOD_POST,
            server: [
                'CONTENT_TYPE' => 'application/ld+json',
                'HTTP_ACCEPT' => 'application/ld+json',
            ],
            content: json_encode([
                'book' => sprintf('/api/books/%d', $bookId),
            ], JSON_THROW_ON_ERROR)
        );
    }
}
