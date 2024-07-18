<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters;

use function is_object;

/**
 * @internal
 *
 * @template TParameters of Parameters
 */
class ResolvedInstruction {
    /**
     * @var class-string<Instruction<TParameters>>
     */
    private readonly string $class;
    /**
     * @var Instruction<TParameters>|null
     */
    private ?Instruction $instance = null;

    /**
     * @param Instruction<TParameters>|class-string<Instruction<TParameters>> $instruction
     */
    public function __construct(
        protected readonly ContainerResolver $container,
        Instruction|string $instruction,
    ) {
        if (is_object($instruction)) {
            $this->class    = $instruction::class;
            $this->instance = $instruction;
        } else {
            $this->class = $instruction;
        }
    }

    /**
     * @return Instruction<TParameters>
     */
    public function getInstance(): Instruction {
        $this->instance ??= $this->container->getInstance()->make($this->class);

        return $this->instance;
    }

    /**
     * @return class-string<Instruction<TParameters>>
     */
    public function getClass(): string {
        return $this->class;
    }
}
