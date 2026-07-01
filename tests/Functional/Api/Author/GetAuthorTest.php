<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Author;

use App\Kernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class GetAuthorTest extends TestCase
{
    public function testItReturnsAuthor(): void
    {
        $kernel = new Kernel('dev', false);
        $request = Request::create(
            '/api/authors/1',
            Request::METHOD_GET,
            server: ['HTTP_ACCEPT' => 'application/ld+json']
        );

        try {
            $response = $kernel->handle($request);
            $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            self::assertSame(200, $response->getStatusCode());
            self::assertSame(1, $data['id']);
            self::assertSame('William', $data['name']);
            self::assertSame('Shakespeare', $data['surname']);
        } finally {
            if (isset($response)) {
                $kernel->terminate($request, $response);
            }

            $kernel->shutdown();
        }
    }
}
