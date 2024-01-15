<?php declare(strict_types = 1);

namespace Larastan\Larastan\ReturnTypes;

use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\Dev\PhpStan\Container\Extension;
use Override;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

/**
 * Original class uses {@see Container::make()} it is slow and unwanted
 *
 * @see Extension
 * @internal
 */
class ContainerMakeDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension {
    #[Override]
    public function getClass(): string {
        return Container::class;
    }

    #[Override]
    public function isMethodSupported(MethodReflection $methodReflection): bool {
        return false;
    }

    #[Override]
    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope,
    ): ?Type {
        return null;
    }
}
