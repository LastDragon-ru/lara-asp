<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use Override;

class ArrayDataProvider extends BaseDataProvider {
    /**
     * @var array<array-key, array<array-key, mixed>>
     */
    private array $data;

    /**
     * @param array<array-key, array<array-key, mixed>> $data
     */
    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * @return array<array-key, array<array-key, mixed>>
     */
    #[Override]
    public function getData(bool $raw = false): array {
        return $this->replaceExpectedValues($this->data, $raw);
    }
}
