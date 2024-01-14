<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\PhpStan\Container;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Override;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

use function count;
use function in_array;
use function is_object;

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
        return in_array($methodReflection->getName(), ['make', 'makeWith', 'resolve'], true);
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
            $argType->isClassStringType()->yes()  => $argType->getClassStringObjectType(),
            $argType->getConstantStrings() !== [] => $this->getFromContainer($argType->getConstantStrings()),
            default                               => null,
        };
    }

    /**
     * @param list<ConstantStringType> $constants
     */
    protected function getFromContainer(array $constants): ?ObjectType {
        // Unions are not supported
        if (count($constants) !== 1) {
            return null;
        }

        // In the most cases, `$abstract` is the class/interface, but there are
        // few of them which are not.
        $abstract = $constants[0]->getValue();
        $strings  = [
            'migration.repository',
            'migrator',
        ];
        $type     = null;

        if (in_array($abstract, $strings, true)) {
            try {
                $concrete = Container::getInstance()->make($abstract);

                if (is_object($concrete)) {
                    $type = new ObjectType($concrete::class);
                }
            } catch (BindingResolutionException) {
                // ignore
            }
        }

        return $type;
    }
}
