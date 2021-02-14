<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

class ArrayDataProvider implements DataProvider {
    /**
     * @var array<mixed>
     */
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array {
        return $this->data;
    }
}
