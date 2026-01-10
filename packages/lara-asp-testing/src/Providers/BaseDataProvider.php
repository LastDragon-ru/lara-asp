<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Providers;

use function array_key_first;

abstract class BaseDataProvider implements DataProvider {
    /**
     * @param array<array-key, array<array-key, mixed>> $items
     *
     * @return array<array-key, array<array-key, mixed>>
     */
    protected function replaceExpectedValues(array $items, bool $raw): array {
        return $raw === false
            ? $this->processExpectedValues($items)
            : $items;
    }

    /**
     * @param array<array-key, array<array-key, mixed>> $items
     *
     * @return array<array-key, array<array-key, mixed>>
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
