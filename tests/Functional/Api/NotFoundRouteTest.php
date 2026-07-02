<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Request;

final class NotFoundRouteTest extends FunctionalTestCase
{
    public function testItReturnsJsonErrorForUnknownUrl(): void
    {
        $request = Request::create(
            '/asspi/autsshors/2',
            Request::METHOD_GET,
            server: ['HTTP_ACCEPT' => 'application/json']
        );

        try {
            $response = $this->kernel->handle($request);
            $data = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            self::assertSame(404, $response->getStatusCode());
            self::assertSame(
                'application/problem+json',
                $response->headers->get('Content-Type')
            );
            self::assertSame([
                '@context' => '/api/contexts/Error',
                '@id' => '/api/errors/404',
                '@type' => 'Error',
                'title' => 'The requested URL does not exist',
                'detail' => 'Not Found',
                'status' => 404,
                'type' => '/errors/404',
                'description' => 'Not Found',
            ], $data);
            self::assertArrayNotHasKey('trace', $data);
        } finally {
            if (isset($response)) {
                $this->kernel->terminate($request, $response);
            }
        }
    }
}
