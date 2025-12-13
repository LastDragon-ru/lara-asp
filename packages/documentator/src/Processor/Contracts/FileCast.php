<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Contracts;

/**
 * @template TObject of object
 */
interface FileCast {
    /**
     * @return TObject
     */
    public function __invoke(Resolver $resolver, File $file): object;
}
