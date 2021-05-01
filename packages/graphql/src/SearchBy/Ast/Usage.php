<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use LogicException;
use OutOfBoundsException;

use function array_key_last;
use function array_merge;
use function array_pop;
use function array_push;
use function array_unique;
use function count;
use function end;
use function is_null;
use function sprintf;

use const SORT_REGULAR;

/**
 * @template T
 */
class Usage {
    /**
     * @var array<string,array<T>>
     */
    protected array $types = [];
    /**
     * @var array<array{id:string,type:string,values:array<T>}>
     */
    protected array $stack = [];

    public function __construct() {
        // empty
    }

    /**
     * @return array<T>
     */
    public function get(string $type): array {
        return $this->types[$type] ?? [];
    }

    public function start(string $type): int {
        $this->stack[] = $current = [
            'id'     => count($this->stack),
            'type'   => $type,
            'values' => [],
        ];

        return $current['id'];
    }

    /**
     * @param T $value
     */
    public function addValue(mixed $value): void {
        $current = array_key_last($this->stack);

        if (!is_null($current)) {
            $this->stack[$current]['values'][] = $value;
        }
    }

    /**
     * @param array<T> $values
     */
    public function addValues(array $values): void {
        $current = array_key_last($this->stack);

        if (!is_null($current)) {
            array_push($this->stack[$current]['values'], ...$values);
        }
    }

    public function end(int $id): static {
        // Is valid?
        $current = end($this->stack);

        if (!$current) {
            throw new LogicException('Stack is empty.');
        }

        if ($current['id'] !== $id) {
            throw new OutOfBoundsException(sprintf(
                'Index mismatch: required `%s`, `%s` given.',
                $current['id'],
                $id,
            ));
        }

        // Save values
        $this->update($current['type'], $current['values']);

        // Reset
        array_pop($this->stack);

        // Update parent
        $parent = end($this->stack);

        if ($parent) {
            $this->update($parent['type'], $current['values']);
        }

        // Return
        return $this;
    }

    /**
     * @param array<T> $values
     */
    protected function update(string $type, array $values): void {
        $this->types[$type] = array_unique(array_merge(
            $this->types[$type] ?? [],
            $values,
        ), SORT_REGULAR);
    }
}
