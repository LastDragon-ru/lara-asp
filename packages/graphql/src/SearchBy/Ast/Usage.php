<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Ast;

use LogicException;
use OutOfBoundsException;

use function array_key_last;
use function array_merge;
use function array_pop;
use function array_unique;
use function array_values;
use function count;
use function end;
use function in_array;
use function is_null;
use function sprintf;

use const SORT_REGULAR;

/**
 * @template T
 */
class Usage {
    /**
     * @var array<string,array{types:array<string>,values:array<T>}>
     */
    protected array $types = [];
    /**
     * @var array<array{id:int,type:string,types:array<string>,values:array<T>}>
     */
    protected array $stack = [];

    public function __construct() {
        // empty
    }

    /**
     * @return array<T>
     */
    public function get(string $type): array {
        return array_values(array_unique($this->values($type, []), SORT_REGULAR));
    }

    public function start(string $type): int {
        $previous      = array_key_last($this->stack);
        $this->stack[] = $current = [
            'id'     => count($this->stack),
            'type'   => $type,
            'types'  => [],
            'values' => [],
        ];

        if (!is_null($previous)) {
            $this->stack[$previous]['types'][] = $type;
        }

        return $current['id'];
    }

    public function addType(string $type): void {
        $this->end($this->start($type));
    }

    /**
     * @param T $value
     */
    public function addValue(mixed ...$value): void {
        $current = array_key_last($this->stack);

        if (!is_null($current)) {
            $this->stack[$current]['values'] = array_merge($this->stack[$current]['values'], $value);
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
        $this->types[$current['type']]['types']  = array_merge(
            $this->types[$current['type']]['types'] ?? [],
            $current['types'],
        );
        $this->types[$current['type']]['values'] = array_merge(
            $this->types[$current['type']]['values'] ?? [],
            $current['values'],
        );

        // Reset
        array_pop($this->stack);

        // Return
        return $this;
    }

    /**
     * @param array<T> $stack
     *
     * @return array<T>
     */
    protected function values(string $type, array $stack): array {
        $types  = $this->types[$type]['types'] ?? [];
        $values = $this->types[$type]['values'] ?? [];

        foreach ($types as $t) {
            if (in_array($t, $stack, true)) {
                continue;
            }

            $values = array_merge($values, $this->values($t, array_merge($stack, [$t])));
        }

        return $values;
    }
}
