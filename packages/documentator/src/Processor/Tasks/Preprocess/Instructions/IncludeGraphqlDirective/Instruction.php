<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Context;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Instruction as InstructionContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Exceptions\DependencyIsMissing;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeGraphqlDirective\Exceptions\TargetIsNotDirective;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Printer;
use LastDragon_ru\LaraASP\GraphQLPrinter\Settings\ImmutableSettings;
use Override;

use function mb_substr;
use function trim;

/**
 * Includes the definition of the directive as a Markdown code block.
 *
 * @implements InstructionContract<Parameters>
 */
class Instruction implements InstructionContract {
    public function __construct(
        protected readonly ?Printer $printer = null,
    ) {
        // empty
    }

    #[Override]
    public static function getName(): string {
        return 'include:graphql-directive';
    }

    #[Override]
    public static function getParameters(): string {
        return Parameters::class;
    }

    #[Override]
    public function __invoke(Context $context, string $target, mixed $parameters): string {
        // Dependencies?
        if ($this->printer === null) {
            throw new DependencyIsMissing($context, Printer::class);
        }

        // Directive?
        $directive  = mb_substr($target, 1);
        $definition = $this->printer->getDirectiveResolver()?->getDefinition($directive);

        if ($definition === null) {
            throw new TargetIsNotDirective($context);
        }

        // Print
        $origin = $this->printer->getSettings();

        try {
            $settings = ImmutableSettings::createFrom($origin)->setPrintDirectives(false);
            $exported = trim((string) $this->printer->setSettings($settings)->export($definition));
            $markdown = <<<MARKDOWN
                ```graphql
                {$exported}
                ```
                MARKDOWN;
        } finally {
            $this->printer->setSettings($origin);
        }

        // Return
        return $markdown;
    }
}
