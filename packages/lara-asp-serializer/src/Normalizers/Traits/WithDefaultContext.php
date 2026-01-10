<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers\Traits;

trait WithDefaultContext {
    /**
     * @var array<array-key, mixed>
     */
    private array $defaultContext = [
        // empty
    ];

    /**
     * @param array<array-key, mixed> $defaultContext
     */
    public function setDefaultContext(array $defaultContext): static {
        $this->defaultContext = $defaultContext;

        return $this;
    }

    /**
     * @param array<array-key, mixed> $context
     *
     * @return array<array-key, mixed>
     */
    public function getContext(array $context): array {
        return $context + $this->defaultContext;
    }

    /**
     * @param array<array-key, mixed> $context
     */
    public function getContextOption(array $context, string $option, mixed $default): mixed {
        return $this->getContext($context)[$option] ?? $default;
    }
}
