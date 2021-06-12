<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use function array_key_first;

abstract class BaseDataProvider implements DataProvider {
    /**
     * @param array<string|int,array<mixed>> $items
     *
     * @return array<string|int,array<mixed>>
     */
    protected function replaceExpectedValues(array $items, bool $raw): array {
        return $raw === false
            ? $this->processExpectedValues($items)
            : $items;
    }

    /**
     * @param array<string|int,array<mixed>> $items
     *
     * @return array<string|int,array<mixed>>
     */
    private function processExpectedValues(array $items): array {
        foreach ($items as $name => $args) {
            $key                = array_key_first($args);
            $items[$name][$key] = $this->getExpectedValue($args[$key]);
        }

        return $items;
    }

    protected function getExpectedValue(mixed $expected): mixed {
        return $expected instanceof ExpectedValue
            ? $expected->getValue()
            : $expected;
    }
}
