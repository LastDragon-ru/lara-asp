<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer;

use Exception;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
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
    public function serialize(Serializable $serializable, string $format = null, array $context = []): string {
        $format ??= $this->format;
        $context += $this->context;

        try {
            return $this->serializer->serialize($serializable, $format, $context);
        } catch (FailedToSerialize $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new FailedToSerialize($serializable, $format, $context, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function deserialize(
        string $serializable,
        string $data,
        string $format = null,
        array $context = [],
    ): Serializable {
        $format ??= $this->format;
        $context += $this->context;

        try {
            return $this->serializer->deserialize($data, $serializable, $format, $context);
        } catch (FailedToDeserialize $exception) {
            throw $exception;
        } catch (Exception $exception) {
            throw new FailedToDeserialize($serializable, $data, $format, $context, $exception);
        }
    }
}
