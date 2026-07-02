<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

final readonly class RoutingExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 64],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (
            $exception instanceof NotFoundHttpException
            && $exception->getPrevious() instanceof ResourceNotFoundException
        ) {
            $event->setResponse($this->createErrorResponse(
                Response::HTTP_NOT_FOUND,
                'The requested URL does not exist',
                'Not Found'
            ));

            return;
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            $event->setResponse($this->createErrorResponse(
                Response::HTTP_METHOD_NOT_ALLOWED,
                'Method Not Allowed',
                'The requested HTTP method is not allowed.',
                $exception->getHeaders()
            ));
        }
    }

    /**
     * @param array<string, string> $headers
     */
    private function createErrorResponse(
        int $status,
        string $title,
        string $detail,
        array $headers = []
    ): JsonResponse {
        return new JsonResponse(
            [
                '@context' => '/api/contexts/Error',
                '@id' => sprintf('/api/errors/%d', $status),
                '@type' => 'Error',
                'title' => $title,
                'detail' => $detail,
                'status' => $status,
                'type' => sprintf('/errors/%d', $status),
                'description' => $detail,
            ],
            $status,
            ['Content-Type' => 'application/problem+json', ...$headers]
        );
    }
}
