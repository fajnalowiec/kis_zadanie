<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Book;

use App\Kernel;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class CreateBookTest extends TestCase
{
    public function testItCreatesBook(): void
    {
        $kernel = new Kernel('dev', false);
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
            $response = $kernel->handle($request);
            $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            self::assertSame(201, $response->getStatusCode());
            self::assertSame('The Winter\'s Tale', $data['title']);
            self::assertSame('/api/authors/1', $data['author']);
            self::assertIsInt($data['id']);
        } finally {
            if (isset($response)) {
                $kernel->terminate($request, $response);
            }

            $kernel->shutdown();

            if (isset($data['id'])) {
                $databaseUrl = getenv('DATABASE_URL');
                self::assertIsString($databaseUrl);

                $connection = DriverManager::getConnection(
                    (new DsnParser(['postgresql' => 'pdo_pgsql']))->parse($databaseUrl)
                );
                $connection->executeStatement('DELETE FROM book WHERE id = ?', [$data['id']]);
                $connection->executeStatement(
                    sprintf(
                        'ALTER TABLE book ALTER COLUMN id RESTART WITH %d',
                        $data['id']
                    )
                );
                $connection->close();
            }
        }
    }
}
