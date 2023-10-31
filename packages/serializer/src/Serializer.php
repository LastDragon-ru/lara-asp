<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer;

use Exception;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer as SerializerContract;
use LastDragon_ru\LaraASP\Serializer\Exceptions\FailedToDeserialize;
use LastDragon_ru\LaraASP\Serializer\Exceptions\FailedToSerialize;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class Serializer implements SerializerContract {
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private SymfonySerializer $serializer,
        private string $format,
        private array $context = [],
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    public function serialize(object $object, string $format = null, array $context = []): string {
        $format ??= $this->format;
        $context += $this->context;

        try {
            return $this->serializer->serialize($object, $format, $context);
        } catch (FailedToSerialize $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new FailedToSerialize($object, $format, $context, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function deserialize(
        string $object,
        string $data,
        string $format = null,
        array $context = [],
    ): object {
        $format ??= $this->format;
        $context += $this->context;

        try {
            return $this->serializer->deserialize($data, $object, $format, $context);
        } catch (FailedToDeserialize $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new FailedToDeserialize($object, $data, $format, $context, $exception);
        }
    }
}
