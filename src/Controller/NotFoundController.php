<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class NotFoundController
{
    #[Route(
        path: '/{path}',
        name: 'app_not_found',
        requirements: ['path' => '.*'],
        priority: -255
    )]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(
            [
                '@context' => '/api/contexts/Error',
                '@id' => '/api/errors/404',
                '@type' => 'Error',
                'title' => 'The requested URL does not exist',
                'detail' => 'Not Found',
                'status' => Response::HTTP_NOT_FOUND,
                'type' => '/errors/404',
                'description' => 'Not Found',
            ],
            Response::HTTP_NOT_FOUND,
            ['Content-Type' => 'application/problem+json']
        );
    }
}
