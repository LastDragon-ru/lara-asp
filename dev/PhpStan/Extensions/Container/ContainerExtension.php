<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\PhpStan\Extensions\Container;

use Illuminate\Contracts\Container\Container;
use NunoMaduro\Larastan\Concerns\HasContainer;
use Override;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ErrorType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Throwable;

use function is_object;

/**
 * @internal
 */
final class ContainerExtension implements DynamicMethodReturnTypeExtension {
    use HasContainer;

    #[Override]
    public function getClass(): string {
        return Container::class;
    }

    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection): bool {
        return $methodReflection->getName() === 'make'
            || $methodReflection->getName() === 'get';
    }

    #[Override]
    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope,
    ): Type {
        $arg  = $methodCall->args[0] ?? null;
        $expr = $arg instanceof Arg ? $arg->value : null;

        if ($expr instanceof String_) {
            try {
                $resolved = $this->resolve($expr->value);

                if ($resolved === null) {
                    return new ErrorType();
                }

                return is_object($resolved)
                    ? new ObjectType($resolved::class)
                    : new ErrorType();
            } catch (Throwable) {
                return new ErrorType();
            }
        }

        if ($expr instanceof ClassConstFetch && $expr->class instanceof FullyQualified) {
            return new ObjectType($expr->class->toString());
        }

        return new NeverType();
    }
}
