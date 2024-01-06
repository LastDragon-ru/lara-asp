<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers;

use Symfony\Component\Serializer\Context\ContextBuilderInterface;
use Symfony\Component\Serializer\Context\ContextBuilderTrait;

class UnitEnumNormalizerContextBuilder implements ContextBuilderInterface {
    use ContextBuilderTrait;

    public function withAllowInvalidValues(bool $allowInvalidValues): static {
        return $this->with(UnitEnumNormalizer::ContextAllowInvalidValues, $allowInvalidValues);
    }
}
