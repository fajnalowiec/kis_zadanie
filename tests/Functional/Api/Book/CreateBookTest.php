<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Book;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;

final class CreateBookTest extends FunctionalTestCase
{
    public function testItCreatesBook(): void
    {
        $request = Request::create(
            '/api/books',
            Request::METHOD_POST,
            server: [
                'CONTENT_TYPE' => 'application/ld+json',
                'HTTP_ACCEPT' => 'application/ld+json',
            ],
            content: json_encode([
                'title' => 'The Winter\'s Tale',
                'author' => '/api/authors/1',
            ], JSON_THROW_ON_ERROR)
        );

        try {
            $response = $this->kernel->handle($request);
            $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            self::assertSame(201, $response->getStatusCode());
            self::assertSame('The Winter\'s Tale', $data['title']);
            self::assertSame('/api/authors/1', $data['author']);
            self::assertIsInt($data['id']);
        } finally {
            if (isset($response)) {
                $this->kernel->terminate($request, $response);
            }
        }
    }
}
