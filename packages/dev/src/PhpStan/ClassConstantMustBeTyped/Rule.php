<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Dev\PhpStan\ClassConstantMustBeTyped;

use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\Dev\Package;
use Override;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ClassConstantsNode;
use PHPStan\Rules\Rule as RuleContract;
use PHPStan\Rules\RuleErrorBuilder;

use function reset;
use function sprintf;

/**
 * Makes the `type` required for class constants (the type is required on level 10).
 *
 * @see https://github.com/slevomat/coding-standard/issues/1701
 * @see https://github.com/slevomat/coding-standard/pull/1702
 *
 * @internal
 * @implements RuleContract<ClassConstantsNode>
 */
class Rule implements RuleContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public function getNodeType(): string {
        return ClassConstantsNode::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function processNode(Node $node, Scope $scope): array {
        // Typed?
        $untyped = [];

        foreach ($node->getConstants() as $constant) {
            if ($constant->type === null) {
                $untyped[] = $constant;
            }
        }

        if ($untyped !== []) {
            $id       = Str::camel(Package::Name).'.classConstantMustBeTyped';
            $class    = $node->getClassReflection()->getName();
            $message  = 'Class constant `%s::%s` must be typed.';
            $messages = [];

            foreach ($untyped as $constant) {
                $const      = reset($constant->consts);
                $name       = $const !== false ? $const->name : 'Unknown O_O';
                $messages[] = RuleErrorBuilder::message(
                    sprintf($message, $class, $name),
                )
                    ->line($constant->getLine())
                    ->identifier($id)
                    ->build();
            }

            return $messages;
        }

        // Nope
        return [];
    }
}
