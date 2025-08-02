<?php

declare(strict_types=1);

namespace App\Serializer;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer as SymfonyAbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer as SymfonyAbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class DoctrineEntitySerializer
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serializeEntity(mixed $entity): string
    {
        return $this->serializer->serialize(
            $entity,
            'json',
            [
                SymfonyAbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function (
                    object $object,
                ): string {
                    return get_class($object);
                },
                SymfonyAbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
                SymfonyAbstractNormalizer::CIRCULAR_REFERENCE_LIMIT => 1,
            ]
        );
    }
}
