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
            self::assertIsInt($firstPage['totalItems']);
            self::assertGreaterThanOrEqual(14, $firstPage['totalItems']);
            self::assertCount(5, $firstPage['member']);
            self::assertSame(
                '/api/books?itemsPerPage=100&page=2',
                $firstPage['view']['next']
            );
            self::assertArrayHasKey('last', $firstPage['view']);

            $kernel->terminate($firstPageRequest, $firstPageResponse);
            unset($firstPageResponse);

            $secondPageRequest = Request::create(
                '/api/books?page=2',
                Request::METHOD_GET,
                server: ['HTTP_ACCEPT' => 'application/ld+json']
            );
            $secondPageResponse = $kernel->handle($secondPageRequest);
            $secondPage = json_decode(
                $secondPageResponse->getContent(),
                true,
                flags: JSON_THROW_ON_ERROR
            );

            self::assertSame(200, $secondPageResponse->getStatusCode());
            self::assertCount(5, $secondPage['member']);
        } finally {
            if (isset($secondPageRequest, $secondPageResponse)) {
                $kernel->terminate($secondPageRequest, $secondPageResponse);
            }

            $kernel->shutdown();
        }
    }
}
