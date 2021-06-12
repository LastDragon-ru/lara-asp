<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

class ArrayDataProvider extends BaseDataProvider {
    /**
     * @var array<array<mixed>>
     */
    private array $data;

    /**
     * @param array<array<mixed>> $data
     */
    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * @return array<array<mixed>>
     */
    public function getData(bool $raw = false): array {
        return $this->replaceExpectedValues($this->data, $raw);
    }
}
