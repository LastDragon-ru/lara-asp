<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Contracts;

/**
 * @template-covariant TValue
 */
interface FieldArgumentDirective {
    /**
     * @return ($value is null ? null : TValue)
     */
    public function getFieldArgumentValue(mixed $value): mixed;

    /**
     * @return TValue
     */
    public function getFieldArgumentDefault(): mixed;
}
