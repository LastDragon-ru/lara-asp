<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;

/**
 * @template TParameters
 * @template TTargetValue
 * @template TTargetResolver of TargetResolver<TParameters, TTargetValue>
 */
interface Instruction {
    public static function getName(): string;

    /**
     * @return class-string<TTargetResolver>
     */
    public static function getTarget(): string;

    /**
     * @return class-string<TParameters>|null
     */
    public static function getParameters(): ?string;

    /**
     * @param Context      $context
     * @param TTargetValue $target
     * @param TParameters  $parameters
     */
    public function process(Context $context, mixed $target, mixed $parameters): string;
}
