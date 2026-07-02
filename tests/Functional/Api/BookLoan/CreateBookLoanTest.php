<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\BookLoan;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;

final class CreateBookLoanTest extends FunctionalTestCase
{
    public function testItBorrowsAvailableBook(): void
    {
        $request = $this->createRequest(100001, 100000);

        try {
            $response = $this->kernel->handle($request);
            $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            self::assertSame(201, $response->getStatusCode());
            self::assertIsInt($data['id']);
            self::assertArrayNotHasKey('returnedAt', $data);
        } finally {
            if (isset($response)) {
                $this->kernel->terminate($request, $response);
            }
        }
    }

    public function testItRejectsAlreadyBorrowedBook(): void
    {
        $request = $this->createRequest(100003, 100000);

        try {
            $response = $this->kernel->handle($request);
            $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            self::assertSame(409, $response->getStatusCode());
            self::assertSame('Book is already borrowed.', $data['detail']);
            self::assertArrayNotHasKey('trace', $data);
        } finally {
            if (isset($response)) {
                $this->kernel->terminate($request, $response);
            }
        }
    }

    public function testItBorrowsReturnedBookAgain(): void
    {
        $request = $this->createRequest(100000, 100001);

        try {
            $response = $this->kernel->handle($request);

            self::assertSame(201, $response->getStatusCode());
        } finally {
            if (isset($response)) {
                $this->kernel->terminate($request, $response);
            }
        }
    }

    public function testItRejectsNonexistentBook(): void
    {
        $request = $this->createRequest(999999, 100000);

        try {
            $response = $this->kernel->handle($request);
            $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            self::assertSame(400, $response->getStatusCode());
            self::assertArrayNotHasKey('trace', $data);
        } finally {
            if (isset($response)) {
                $this->kernel->terminate($request, $response);
            }
        }
    }

    public function testItRejectsNonexistentCustomer(): void
    {
        $request = $this->createRequest(100001, 999999);

        try {
            $response = $this->kernel->handle($request);

            self::assertSame(400, $response->getStatusCode());
        } finally {
            if (isset($response)) {
                $this->kernel->terminate($request, $response);
            }
        }
    }

    private function createRequest(int $bookId, int $customerId): Request
    {
        return Request::create(
            '/api/book-loans/borrow',
            Request::METHOD_POST,
            server: [
                'CONTENT_TYPE' => 'application/ld+json',
                'HTTP_ACCEPT' => 'application/ld+json',
            ],
            content: json_encode([
                'book' => sprintf('/api/books/%d', $bookId),
                'customer' => sprintf('/api/customers/%d', $customerId),
            ], JSON_THROW_ON_ERROR)
        );
    }
}
