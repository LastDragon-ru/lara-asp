<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers;

use Symfony\Component\Serializer\Context\ContextBuilderInterface;
use Symfony\Component\Serializer\Context\ContextBuilderTrait;

final class CarbonNormalizerContextBuilder implements ContextBuilderInterface {
    use ContextBuilderTrait;

    /**
     * @see https://secure.php.net/manual/en/datetime.format.php
     */
    public function withFormat(?string $format): static {
        return $this->with(CarbonNormalizer::ContextFormat, $format);
    }

    public function withFallback(?bool $fallback): static {
        return $this->with(CarbonNormalizer::ContextFallback, $fallback);
    }
}
