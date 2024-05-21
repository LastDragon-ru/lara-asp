<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Instruction;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver;

use function is_object;

/**
 * @internal
 *
 * @template TTarget
 * @template TParameters of object|null
 */
class ResolvedInstruction {
    /**
     * @var class-string<Instruction<TTarget, TParameters>>
     */
    private readonly string $class;
    /**
     * @var Resolver<TParameters, TTarget>|Resolver<null, TTarget>|null
     */
    private ?Resolver $resolver = null;
    /**
     * @var Instruction<TTarget, TParameters>|null
     */
    private ?Instruction $instance = null;

    /**
     * @param Instruction<TTarget, TParameters>|class-string<Instruction<TTarget, TParameters>> $instruction
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
     * @return Instruction<TTarget, TParameters>
     */
    public function getInstance(): Instruction {
        $this->instance ??= $this->container->getInstance()->make($this->class);

        return $this->instance;
    }

    /**
     * @return Resolver<TParameters, TTarget>|Resolver<null, TTarget>
     */
    public function getResolver(): Resolver {
        $this->resolver ??= $this->container->getInstance()->make($this->class::getResolver());

        return $this->resolver;
    }

    /**
     * @return class-string<Instruction<TTarget, TParameters>>
     */
    public function getClass(): string {
        return $this->class;
    }
}
