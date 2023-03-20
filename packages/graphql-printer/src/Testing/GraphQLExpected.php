<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Testing;

use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

abstract class GraphQLExpected {
    /**
     * @param array<string>|null $usedTypes
     * @param array<string>|null $usedDirectives
     */
    public function __construct(
        protected ?array $usedTypes = null,
        protected ?array $usedDirectives = null,
        protected ?Settings $settings = null,
    ) {
        // empty
    }

    /**
     * @return array<string>|null
     */
    public function getUsedTypes(): ?array {
        return $this->usedTypes;
    }

    /**
     * @param array<string>|null $usedTypes
     */
    public function setUsedTypes(?array $usedTypes): static {
        $this->usedTypes = $usedTypes;

        return $this;
    }

    /**
     * @return array<string>|null
     */
    public function getUsedDirectives(): ?array {
        return $this->usedDirectives;
    }

    /**
     * @param array<string>|null $usedDirectives
     */
    public function setUsedDirectives(?array $usedDirectives): static {
        $this->usedDirectives = $usedDirectives;

        return $this;
    }

    public function getSettings(): ?Settings {
        return $this->settings;
    }

    public function setSettings(?Settings $settings): static {
        $this->settings = $settings;

        return $this;
    }
}
