<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Book;

use App\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class GetBooksTest extends TestCase
{
    public function testItReturnsPaginatedBooksUsingConfiguredPageSize(): void
    {
        $kernel = new Kernel('dev', false);
        $firstPageRequest = Request::create(
            '/api/books?page=1&itemsPerPage=100',
            Request::METHOD_GET,
            server: ['HTTP_ACCEPT' => 'application/ld+json']
        );

        try {
            $firstPageResponse = $kernel->handle($firstPageRequest);
            $firstPage = json_decode(
                $firstPageResponse->getContent(),
                true,
                flags: JSON_THROW_ON_ERROR
            );

            self::assertSame(200, $firstPageResponse->getStatusCode());
            self::assertSame(14, $firstPage['totalItems']);
            self::assertCount(5, $firstPage['member']);
            self::assertSame(
                '/api/books?itemsPerPage=100&page=2',
                $firstPage['view']['next']
            );
            self::assertSame(
                '/api/books?itemsPerPage=100&page=3',
                $firstPage['view']['last']
            );

            $kernel->terminate($firstPageRequest, $firstPageResponse);
            unset($firstPageResponse);

            $lastPageRequest = Request::create(
                '/api/books?page=3',
                Request::METHOD_GET,
                server: ['HTTP_ACCEPT' => 'application/ld+json']
            );
            $lastPageResponse = $kernel->handle($lastPageRequest);
            $lastPage = json_decode(
                $lastPageResponse->getContent(),
                true,
                flags: JSON_THROW_ON_ERROR
            );

            self::assertSame(200, $lastPageResponse->getStatusCode());
            self::assertCount(4, $lastPage['member']);
            self::assertArrayNotHasKey('next', $lastPage['view']);
        } finally {
            if (isset($lastPageRequest, $lastPageResponse)) {
                $kernel->terminate($lastPageRequest, $lastPageResponse);
            }

            $kernel->shutdown();
        }
    }
}
