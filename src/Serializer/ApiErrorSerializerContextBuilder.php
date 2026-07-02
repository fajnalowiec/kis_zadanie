<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Metadata\Error;
use ApiPlatform\State\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

final readonly class ApiErrorSerializerContextBuilder implements SerializerContextBuilderInterface
{
    private const SENSITIVE_ATTRIBUTES = [
        'trace',
        'traceAsString',
        'file',
        'line',
        'code',
        'message',
    ];

    public function __construct(
        private SerializerContextBuilderInterface $decorated
    ) {
    }

    public function createFromRequest(
        Request $request,
        bool $normalization,
        ?array $extractedAttributes = null
    ): array {
        $context = $this->decorated->createFromRequest(
            $request,
            $normalization,
            $extractedAttributes
        );

        if (!$normalization || !$context['operation'] instanceof Error) {
            return $context;
        }

        if (isset($context['groups'])) {
            $context['groups'] = array_values(
                array_diff((array) $context['groups'], ['trace'])
            );
        }

        $ignoredAttributes = $context[AbstractObjectNormalizer::IGNORED_ATTRIBUTES] ?? [];
        $context[AbstractObjectNormalizer::IGNORED_ATTRIBUTES] = array_values(
            array_unique([...$ignoredAttributes, ...self::SENSITIVE_ATTRIBUTES])
        );

        return $context;
    }
}
