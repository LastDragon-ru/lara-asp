<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\PhpStan\Container;

use Illuminate\Contracts\Container\Container as ContainerContract;
use Override;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

use function in_array;

/**
 * Provides proper return types for {@see Container::make()} without resolving
 * where possible.
 *
 * @see https://github.com/nunomaduro/larastan/pull/945
 * @see https://github.com/nunomaduro/larastan/issues/941
 * @see https://github.com/phpstan/phpstan/discussions/10410
 *
 * @internal
 */
final class Extension implements DynamicMethodReturnTypeExtension {
    public function __construct() {
        // empty
    }

    #[Override]
    public function getClass(): string {
        return ContainerContract::class;
    }

    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection): bool {
        return in_array($methodReflection->getName(), ['make', 'makeWith', 'resolve', 'call'], true);
    }

    #[Override]
    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope,
    ): ?Type {
        // Type?
        $arg     = $methodCall->args[0] ?? null;
        $argExpr = $arg instanceof Arg ? $arg->value : null;
        $argType = $argExpr instanceof Expr ? $scope->getType($argExpr) : null;

        if ($argType === null) {
            return null;
        }

        // Return
        return match (true) {
            $methodReflection->getName() === 'call' => match (true) {
                $argType->isCallable()->yes() => ParametersAcceptorSelector
                    ::selectFromArgs(
                        $scope,
                        $methodCall->getArgs(),
                        $argType->getCallableParametersAcceptors($scope),
                    )
                    ->getReturnType(),
                default                       => null,
            },
            default                                 => match (true) {
                $argType->isClassString()->yes() => $argType->getClassStringObjectType(),
                default                          => null,
            },
        };
    }
}
